<?php

namespace Modules\SeatingPlan\Services\TableViewer;


use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\Branch\Models\Branch;
use Modules\Order\Enums\OrderProductStatus;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\OrderType;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderDiscount;
use Modules\Order\Models\OrderProduct;
use Modules\Order\Models\OrderTax;
use Modules\Order\Services\SaveOrder\SaveOrderServiceInterface;
use Modules\Payment\Models\Payment;
use Modules\Pos\Enums\PosSubmitAction;
use Modules\SeatingPlan\Enums\TableMergeType;
use Modules\SeatingPlan\Enums\TableStatus;
use Modules\SeatingPlan\Events\TableAssignWaiter;
use Modules\SeatingPlan\Models\Floor;
use Modules\SeatingPlan\Models\Table;
use Modules\SeatingPlan\Models\TableMerge;
use Modules\SeatingPlan\Models\Zone;
use Modules\SeatingPlan\Services\Table\TableServiceInterface;

class TableViewerService implements TableViewerServiceInterface
{
    /**
     * Create a new instance of TableViewerService
     *
     * @param TableServiceInterface $service
     */
    public function __construct(protected TableServiceInterface $service)
    {

    }

    /** @inheritDoc */
    public function show(int $id): Table
    {
        $table = $this->service->getModel()
            ->with([
                "waiter:id,name",
                "floor:id,name",
                "zone:id,name",
                "currentMerge" => fn($query) => $query->with(["members" => fn($query) => $query->with("table:id,name")]),
                "activeOrder" => fn($query) => $query->with([
                    "products" => fn($query) => $query
                        ->whereNotIn("status", [OrderProductStatus::Cancelled, OrderProductStatus::Refunded])
                        ->without("taxes", "options"),
                    "customer:id,name",
                    "table:id,name",
                    "tableMerge:id,type",
                ])
            ])
            ->findOrFail($id);

        $with = [
            "products" => fn($query) => $query
                ->whereNotIn("status", [OrderProductStatus::Cancelled, OrderProductStatus::Refunded])
                ->without("taxes", "options"),
            "customer:id,name",
            "table:id,name"
        ];

        $viewerOrder = null;
        $viewerOrders = null;
        if (!is_null($table->current_merge_id) && !is_null($table->currentMerge)) {
            switch ($table->currentMerge->type) {
                case TableMergeType::Order:
                    $viewerOrder = Order::query()
                        ->where('table_id', $table->currentMerge->table_id)
                        ->activeOrders()
                        ->with($with)
                        ->first();
                    break;
                case TableMergeType::Billing:
                    $viewerOrders = Order::query()
                        ->where('table_merge_id', $table->current_merge_id)
                        ->with($with)
                        ->activeOrders()
                        ->get();
                    break;
                case TableMergeType::Capacity:
                    $viewerOrder = $table->activeOrder;
                    break;
            }
        } else {
            $viewerOrder = $table->activeOrder;
        }

        $table->setRelation('viewerOrder', $viewerOrder);
        $table->setRelation('viewerOrders', ($viewerOrders?->isNotEmpty() ?? false) ? $viewerOrders : null);

        return $table;
    }

    /** @inheritDoc */
    public function findOrFail(int $id): Builder|array|EloquentCollection|Table
    {
        return $this->service->getModel()->query()->findOrFail($id);
    }

    /** @inheritDoc */
    public function assignWaiter(int $id, array $data): void
    {
        $table = $this->service->findOrFail($id);
        $oldWaiterId = $table->assigned_waiter_id;
        $waiterId = $data['waiter_id'] ?? null;

        $table->update(['assigned_waiter_id' => $waiterId]);

        if (!is_null($table->activeOrder)) {
            $table->activeOrder->update(['waiter_id' => $waiterId]);
        }

        event(new TableAssignWaiter(
            table: $table,
            waiterId: ($data['waiter_id'] ?? null),
            oldWaiterId: $oldWaiterId
        ));
    }

    /** @inheritDoc */
    public function merge(int $id, array $data): void
    {
        DB::transaction(function () use ($id, $data) {
            $user = auth()->user();
            $branch = $user->assignedToBranch() ? $user->branch : Branch::find($data['branch_id']);
            $allTables = $this->service
                ->getModel()
                ->query()
                ->whereIn('id', [$id, ...$data['table_ids']])
                ->where('branch_id', $branch->id)
                ->availableMerge()
                ->lockForUpdate()
                ->get();

            $mainTable = $allTables->first(fn(Table $table) => $table->id === $id);

            abort_if($mainTable->current_merge_id, 400, __("seatingplan::messages.main_table_is_already_merged"));

            $type = TableMergeType::from($data['type']);
            $tables = $allTables->filter(fn(Table $table) => $table->id !== $id);
            $tableIds = $tables->pluck("id")->toArray();
            $allTableIds = [$mainTable->id, ...$tableIds];

            /** @var TableMerge $merge */
            $merge = $mainTable
                ->merges()
                ->create(["branch_id" => $branch->id, "type" => $type]);

            $allTables->each(fn(Table $table) => $merge->members()
                ->create([
                    "table_id" => $table->id,
                    "is_main" => $table->id == $mainTable->id,
                ]));
            switch ($type) {
                case TableMergeType::Capacity:
                    $this->service->getModel()->query()
                        ->whereIn('id', $allTableIds)
                        ->update(["current_merge_id" => $merge->id]);
                    break;

                case TableMergeType::Billing:
                    $this->service->getModel()->query()
                        ->whereIn('id', $allTableIds)
                        ->update(["current_merge_id" => $merge->id]);

                    Order::query()
                        ->forMerge()
                        ->whereIn('table_id', $allTableIds)
                        ->update(['table_merge_id' => $merge->id]);
                    break;

                case TableMergeType::Order:
                    $this->service->getModel()->query()
                        ->whereIn('id', $tableIds)
                        ->update([
                            "current_merge_id" => $merge->id,
                            "status" => TableStatus::Merged
                        ]);

                    $tables->each(fn(Table $t) => $t->storeStatusLog(status: TableStatus::Merged));

                    if ($mainTable->status === TableStatus::Occupied) {
                        $mainTable->update(["current_merge_id" => $merge->id]);
                    } else {
                        $hasOrders = Order::query()
                            ->whereIn('table_id', $allTableIds)
                            ->forMerge()
                            ->exists();

                        if ($hasOrders) {
                            $mainTable->update([
                                "status" => TableStatus::Occupied,
                                "current_merge_id" => $merge->id,
                            ]);
                            $mainTable->storeStatusLog(status: TableStatus::Occupied);
                        } else {
                            $mainTable->update(["current_merge_id" => $merge->id]);
                        }
                    }

                    /** @var Order $mainOrder */
                    $mainOrder = Order::query()
                        ->where('table_id', $mainTable->id)
                        ->forMerge()
                        ->first();

                    $otherOrders = Order::query()
                        ->where('id', '!=', $mainOrder?->id)
                        ->whereIn('table_id', $tableIds)
                        ->forMerge()
                        ->get();

                    $otherOrderIds = $otherOrders->pluck('id')->toArray();

                    if (!empty($otherOrderIds)) {
                        $notes = [];
                        if (!is_null($mainOrder) && $mainOrder->notes) {
                            $notes = [$mainOrder->notes];
                        }
                        $notes = [...$notes, ...$otherOrders->pluck('notes')->toArray()];
                        $notes = implode("\n----------------\n", $notes) ?: null;

                        if (is_null($mainOrder)) {
                            $mainOrder = app(SaveOrderServiceInterface::class)
                                ->create([
                                    "branch_id" => $data['branch_id'],
                                    "type" => OrderType::DineIn->value,
                                    "table_id" => $mainTable->id,
                                    "register_id" => $data['register_id'],
                                    "session_id" => $data['session_id'],
                                    "submit_action" => PosSubmitAction::HoldOrder->value,
                                    "guest_count" => $otherOrders->sum("guest_count"),
                                    "notes" => $notes,
                                ]);
                        } else {
                            $mainOrder->update([
                                "guest_count" => $mainOrder->guest_count + $otherOrders->sum("guest_count"),
                                "notes" => $notes,
                            ]);
                        }
                        
                        if (is_null($mainOrder->discount)) {
                            OrderDiscount::query()
                                ->whereIn('order_id', $otherOrderIds)
                                ->first()
                                ->update(['order_id' => $mainOrder->id]);
                        }

                        $deletedDiscounts = OrderDiscount::query()
                            ->whereIn('order_id', $otherOrderIds)
                            ->delete();

                        OrderProduct::query()
                            ->whereIn('order_id', $otherOrderIds)
                            ->update(['order_id' => $mainOrder->id]);

                        Payment::query()
                            ->whereIn('order_id', $otherOrderIds)
                            ->update(['order_id' => $mainOrder->id]);

                        OrderTax::query()
                            ->whereIn('order_id', $otherOrderIds)
                            ->update(['order_id' => $mainOrder->id]);

                        Order::query()
                            ->whereIn('id', $otherOrderIds)
                            ->update([
                                'status' => OrderStatus::Merged,
                                'merged_into_order_id' => $mainOrder->id,
                                'merged_by' => $user->id,
                                'merged_at' => now(),
                            ]);

                        $orderStatusNote = ($deletedDiscounts > 0) ? "ORDER_DISCOUNT_REMOVED_ON_MERGE" : "ORDER_MERGED";

                        /** @var Order $otherOrder */
                        foreach ($otherOrders as $otherOrder) {
                            $otherOrder->storeStatusLog(
                                status: OrderStatus::Merged,
                                changedById: $user->id,
                                note: "$orderStatusNote :$mainOrder->reference_no"
                            );
                        }

                        $mainOrder->recalculate(deleteTaxesDuplicates: true);
                    }
                    break;
            }
        });
    }

    /** @inheritDoc */
    public function get(?int $branchId = null, array $filters = []): array
    {
        $search = trim((string)($filters['search'] ?? ''));
        $floorIds = collect($filters['floors'] ?? [])->filter()->values()->all();
        $zoneIds = collect($filters['zones'] ?? [])->filter()->values()->all();
        $statuses = collect($filters['statuses'] ?? [])->filter()->values()->all();

        $tables = $this->service->getModel()
            ->query()
            ->whereHas("floor", fn($query) => $query->where('is_active', true))
            ->whereHas("zone", fn($query) => $query->where('is_active', true))
            ->with(["floor:id,name", "zone:id,name"])
            ->when(!is_null($branchId), fn($query) => $query->where('branch_id', $branchId))
            ->when($search !== '', fn($query) => $query->search($search))
            ->when(!empty($floorIds), fn($query) => $query->whereIn('floor_id', $floorIds))
            ->when(!empty($zoneIds), fn($query) => $query->whereIn('zone_id', $zoneIds))
            ->when(!empty($statuses), fn($query) => $query->whereIn('status', $statuses))
            ->orderby("order")
            ->get();

        return [
            "tables" => $tables,
            "floors" => Floor::list($branchId),
            "zones" => Zone::list($branchId),
            "statuses" => TableStatus::toArrayTrans(),
        ];
    }

    /** @inheritDoc */
    public function makeAsAvailable(int $id): void
    {
        $table = $this->service->findOrFail($id);

        abort_if(
            $table->status !== TableStatus::Cleaning,
            400,
            __("seatingplan::messages.fail_make_table_as_available", ["status" => $table->status->trans()])
        );

        $table->update(["status" => TableStatus::Available]);
        $table->storeStatusLog(status: TableStatus::Available, changedById: auth()->id());
    }

    /** @inheritDoc */
    public function getMergeMeta(int $id): array
    {
        $table = $this->service->findOrFail($id);
        return [
            "tables" => $this->service
                ->getModel()
                ->query()
                ->with(["floor:id,name", "zone:id,name"])
                ->whereNot('id', $id)
                ->orderBy('order')
                ->orderBy('floor_id')
                ->orderBy('zone_id')
                ->where('branch_id', $table->branch_id)
                ->availableMerge()
                ->get()
                ->map(fn(Table $table) => [
                    "id" => $table->id,
                    "name" => "$table->name  ({$table->floor->name} • {$table->zone->name})",
                ]),
            "types" => TableMergeType::toArrayTrans(),
        ];
    }

    /** @inheritDoc */
    public function splitTable(int $mergeId): void
    {
        DB::transaction(function () use ($mergeId) {
            $user = auth()->user();

            $merge = TableMerge::query()
                ->where("id", $mergeId)
                ->with(["members.table"])
                ->lockForUpdate()
                ->firstOrFail();

            $tables = $merge->members->pluck("table");
            $tableIds = $merge->members->pluck("table_id")->toArray();
            $main = $merge->members->firstWhere("is_main", true)?->table;

            abort_if(!$main, 400, __("seatingplan::messages.main_table_missing"));

            $activeOrders = Order::query()
                ->whereIn('table_id', $tableIds)
                ->activeOrders()
                ->latest()
                ->get();

            abort_if($activeOrders->count() > 0, 400, __("seatingplan::messages.split_failed_active_orders"));

            $this->service
                ->getModel()
                ->whereIn('id', $tableIds)
                ->update([
                    "current_merge_id" => null,
                    "status" => TableStatus::Available
                ]);

            $tables->each(fn(Table $table) => $table->storeStatusLog(status: TableStatus::Available));

            $merge->update(["closed_at" => now(), "closed_by" => $user->id]);
        });
    }
}
