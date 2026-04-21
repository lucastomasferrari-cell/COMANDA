<?php

namespace Modules\Order\Services\SaveOrder;

use DB;
use Illuminate\Support\Collection;
use Modules\Branch\Models\Branch;
use Modules\Cart\CartItem;
use Modules\Cart\Facades\Cart;
use Modules\Currency\Currency;
use Modules\Currency\Models\CurrencyRate;
use Modules\Order\Enums\OrderPaymentStatus;
use Modules\Order\Enums\OrderProductAction;
use Modules\Order\Enums\OrderProductStatus;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\OrderType;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderUpdated;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderProduct;
use Modules\Pos\Enums\PosSubmitAction;
use Modules\SeatingPlan\Enums\TableMergeType;
use Modules\SeatingPlan\Enums\TableStatus;
use Modules\SeatingPlan\Models\Table;
use Modules\User\Enums\DefaultRole;
use Modules\User\Models\User;
use Throwable;

class SaveOrderService implements SaveOrderServiceInterface
{

    /**
     * Create Order
     *
     * @param Branch $branch
     * @param User $user
     * @param array $data
     * @return Order
     */
    private function createOrder(Branch $branch, User $user, array $data): Order
    {
        $isDineIn = $data['type'] == OrderType::DineIn->value;
        $tableMergeId = null;

        $tableId = $isDineIn ? ($data['table_id'] ?? null) : null;
        $waiterId = $user->hasRole(DefaultRole::Waiter->value) ? $user->id : ($data['waiter_id'] ?? null);

        if (!is_null($tableId)) {
            $table = Table::query()->with(["currentMerge", "activeOrder"])->findOrFail($tableId);

            abort_unless(is_null($table->activeOrder), 400, __("order::messages.table_already_have_active_order"));

            if (is_null($waiterId) && !is_null($table->assigned_waiter_id)) {
                $waiterId = $table->assigned_waiter_id;
            } elseif (!is_null($waiterId) && is_null($table->assigned_waiter_id)) {
                $table->assigned_waiter_id = $waiterId;
                $table->save();
            }

            if (!is_null($table->currentMerge) && $table->currentMerge->type == TableMergeType::Billing) {
                $tableMergeId = $table->currentMerge->id;
            }
        }

        return Order::query()
            ->create([
                "branch_id" => $branch->id,
                "table_id" => $tableId,
                "customer_id" => Cart::customer()?->id(),
                "pos_register_id" => $data['register_id'],
                "pos_session_id" => $data['session_id'],
                "waiter_id" => $waiterId,
                "status" => $data['submit_action'] == PosSubmitAction::SendToKitchen->value
                    ? OrderStatus::Confirmed
                    : OrderStatus::Pending,
                "type" => $data['type'],
                "payment_status" => OrderPaymentStatus::Unpaid,
                "currency" => $branch->currency,
                "currency_rate" => $data['currency_rate'],
                "subtotal" => Cart::subTotal()->amount(),
                "total" => Cart::total()->amount(),
                "due_amount" => Cart::total()->amount(),
                ...($data['type'] == OrderType::DriveThru->value
                    ? [
                        "car_plate" => $data['car_plate'],
                        "car_description" => $data['car_description'],
                    ]
                    : []),
                "scheduled_at" => in_array($data['type'], [OrderType::PreOrder->value, OrderType::Catering->value])
                    ? ($data['scheduled_at'] ?? null)
                    : null,
                "guest_count" => $data['guest_count'] ?? 1,
                "notes" => $data["notes"] ?? null,
                "order_date" => now(),
                "served_at" => $isDineIn ? now() : null,
                "table_merge_id" => $tableMergeId
            ]);
    }

    /** @inheritDoc */
    public function create(array $data): Order
    {
        /** @var Branch $branch */
        $branch = Branch::withTrashed()->find($data['branch_id']);
        $user = auth()->user();

        $data['currency_rate'] = CurrencyRate::for($branch->currency);
        if (!isset($data['type'])) {
            $data['type'] = Cart::orderType()->value();
        }

        return DB::transaction(function () use ($data, $user, $branch) {
            $order = $this->createOrder($branch, $user, $data);
            $this->storeStatusLogs($order);
            $this->updateTableStatus($order);
            $this->updateOrCreateProducts($order);
            $order->updateOrCreateTaxes(Cart::taxes());
            $order->UpdateOrCreateDiscount(Cart::discount());

            event(new OrderCreated($order));

            return $order;
        });
    }

    /**
     * Store Order Status Logs
     *
     * @param Order $order
     * @return void
     */
    private function storeStatusLogs(Order $order): void
    {
        $statuses = [OrderStatus::Pending];

        if ($order->status != OrderStatus::Pending) {
            $statuses[] = OrderStatus::Confirmed;
        }

        foreach ($statuses as $status) {
            $order->storeStatusLog(status: $status);
        }
    }

    /**
     * Update table status
     *
     * @param Order $order
     * @return void
     */
    private function updateTableStatus(Order $order): void
    {
        if ($order->type == OrderType::DineIn && !is_null($order->table_id)) {
            if ($order->table->status != TableStatus::Occupied) {
                $order->table->update(["status" => TableStatus::Occupied]);
                $order->table->storeStatusLog(
                    status: TableStatus::Occupied,
                    note: "ORDER_CREATED :$order->reference_no"
                );
            }
        }
    }

    /** @inheritDoc */
    public function update(int $id, array $data): Order
    {
        /** @var Order $order */
        $order = Order::query()->with(["branch", "products"])->findOrFail($id);

        $user = auth()->user();

        $scale = Currency::subunit($order->currency);

        $data['currency_rate'] = CurrencyRate::for($order->branch->currency);
        $data['type'] = Cart::orderType()->value();
        $totalRound = Cart::total()->round()->amount();

        $amountPaid = round($order->total->amount() - $order->due_amount->amount(), $scale);
        $data['overpaid_amount'] = 0;

        if ($amountPaid > $totalRound) {
            $data['overpaid_amount'] = round($amountPaid - $totalRound, $scale);
        }

        abort_if(
            $data['overpaid_amount'] > 0 && !isset($data['refund_payment_method']),
            400,
            __("validation.required", ["attribute" => __("order::attributes.orders.refund_payment_method")])
        );

        abort_if($totalRound <= 0, 400, __("order::messages.order_must_contain_at_least_one_active_product"));

        return DB::transaction(function () use ($data, $order, $user, $id) {
            $order = $this->updateOrder($order, $user, $data);

            $this->updateOrCreateProducts($order, true);

            $order->updateOrCreateTaxes(Cart::taxes());
            $order->updateOrCreateDiscount(Cart::discount());
            $order->revertOrderStatusToPreparingIfModified();
            $order->handleOverpaymentAdjustment($data);

            event(new OrderUpdated($order));

            return $order;
        });
    }

    /**
     * Update Order
     *
     * @param Order $order
     * @param User $user
     * @param array $data
     * @return Order
     */
    private function updateOrder(Order $order, User $user, array $data): Order
    {
        $isDineIn = $data['type'] == OrderType::DineIn->value;
        $total = Cart::total()->amount();
        $tableId = $isDineIn ? ($data['table_id'] ?? null) : null;
        $tableMergeId = $order->table_merge_id;
        $waiterId = $data['waiter_id'] ?? ($user->hasRole(DefaultRole::Waiter->value) && $isDineIn ? $user->id : null);

        if (!is_null($order->table_id) && is_null($tableId)) {
            $this->handleRemoveTableFromOrder($order);
        } else if ($order->table_id && $order->table_id != $tableId) {
            $tableData = $this->handleChangeTableOrder($order, $tableId, $waiterId);
            $waiterId = $tableData['waiter_id'];
            $tableMergeId = $tableData['table_merge_id'];
        }

        $order->update([
            "customer_id" => Cart::customer()?->id(),
            "table_id" => $tableId,
            "pos_register_id" => $data['register_id'],
            "pos_session_id" => $data['session_id'],
            "waiter_id" => $waiterId,
            "type" => $data['type'],
            "currency" => $order->branch->currency,
            "currency_rate" => $data['currency_rate'],
            "subtotal" => Cart::subTotal()->amount(),
            "total" => $total,
            "due_amount" => max($total - ($order->total->amount() - $order->due_amount->amount()), 0),
            ...($data['type'] == OrderType::DriveThru->value
                ? [
                    "car_plate" => $data['car_plate'],
                    "car_description" => $data['car_description'],
                ]
                : []),
            "scheduled_at" => in_array($data['type'], [OrderType::PreOrder->value, OrderType::Catering->value])
                ? ($data['scheduled_at'] ?? null)
                : null,
            "guest_count" => $data['guest_count'] ?? 1,
            "notes" => $data["notes"] ?? null,
            "modified_at" => now(),
            "modified_by" => auth()->id(),
            "table_merge_id" => $tableMergeId
        ]);

        return $order->fresh();
    }

    /**
     * Handle remove table from order
     *
     * @param Order $order
     * @return void
     */
    private function handleRemoveTableFromOrder(Order $order): void
    {
        $table = Table::query()
            ->with(["currentMerge"])
            ->findOrFail($order->table_id);

        abort_if(
            !is_null($table->currentMerge) || !is_null($order->table_merge_id),
            400,
            __("order::messages.table_cannot_be_changed_because_merge_request")
        );

        $table->update(["status" => TableStatus::Available]);
        $table->storeStatusLog(
            status: TableStatus::Available,
            note: "ORDER_REMOVE_TABLE :$order->reference_no"
        );

    }

    /**
     * Handle remove table from order
     *
     * @param Order $order
     * @param int $newTableId
     * @param int|null $waiterId
     * @return array
     */
    private function handleChangeTableOrder(Order $order, int $newTableId, ?int $waiterId): array
    {
        $table = Table::query()
            ->with(["currentMerge"])
            ->findOrFail($order->table_id);

        abort_if(
            !is_null($table->currentMerge) || !is_null($order->table_merge_id),
            400,
            __("order::messages.table_cannot_be_changed_because_merge_request")
        );

        $newTable = Table::query()
            ->with(["currentMerge", "activeOrder"])
            ->findOrFail($newTableId);

        abort_unless(is_null($newTable->activeOrder), 400, __("order::messages.table_already_have_active_order"));

        $tableMergeId = null;

        if (is_null($waiterId) && !is_null($newTable->assigned_waiter_id)) {
            $waiterId = $newTable->assigned_waiter_id;
        } elseif (!is_null($waiterId) && is_null($newTable->assigned_waiter_id)) {
            $newTable->assigned_waiter_id = $waiterId;
            $newTable->save();
        }

        if (!is_null($table->currentMerge) && $table->currentMerge->type == TableMergeType::Billing) {
            $tableMergeId = $table->currentMerge->id;
        }

        if ($newTable->status != TableStatus::Occupied) {
            $newTable->update(["status" => TableStatus::Occupied]);
            $newTable->storeStatusLog(
                status: TableStatus::Occupied,
                note: "ORDER_CHANGE_TABLE :$order->reference_no:$table->id"
            );
        }

        $table->update(["status" => TableStatus::Available]);
        $table->storeStatusLog(
            status: TableStatus::Available,
            note: "ORDER_CHANGE_TABLE :$order->reference_no:$newTable->id"
        );

        return [
            "table_merge_id" => $tableMergeId,
            "waiter_id" => $waiterId,
        ];
    }

    /**
     * Update Or Create Products
     *
     * @param Order $order
     * @param bool $isUpdate
     * @return void
     * @throws Throwable
     */
    private function updateOrCreateProducts(Order $order, bool $isUpdate = false): void
    {
        $items = Cart::items();
        if ($isUpdate) {
            $items = $this->parseProductActions($items, $order->products);
        }

        foreach ($items as $product) {
            $order->updateOrCreateProduct($product);
        }
    }

    /**
     * Parse Product actions
     *
     * @param Collection $items
     * @param Collection $orderProducts
     * @return Collection
     */
    private function parseProductActions(Collection $items, Collection $orderProducts): Collection
    {
        $data = collect();
        $orderProducts = $orderProducts->keyBy('id');
        $itemsMap = $items
            ->filter(fn($item) => !is_null($item->orderProduct))
            ->keyBy(fn($item) => $item->orderProduct->id());

        $deletedProductIds = [];
        /** @var OrderProduct $orderProduct */
        foreach ($orderProducts as $orderProduct) {
            if ($orderProduct->status === OrderProductStatus::Pending && !isset($itemsMap[$orderProduct->id])) {
                $deletedProductIds[] = $orderProduct->id;
            }
        }

        OrderProduct::query()->whereIn('id', $deletedProductIds)->delete();

        /** @var CartItem $item */
        foreach ($items as $item) {

            if (!is_null($item->orderProduct) && isset($orderProducts[$item->orderProduct->id()])) {
                $exceptedQuantity = collect(array_values($item->actions))->sum('quantity');
                /** @var OrderProduct $originalProduct */
                $originalProduct = $orderProducts[$item->orderProduct->id()];
                if ($item->qty == 0) {
                    $item->qty = $exceptedQuantity;
                }
                $quantity = $item->qty;
                if (in_array($originalProduct->status, [OrderProductStatus::Cancelled, OrderProductStatus::Refunded])) {
                    $item->orderProduct->setStatus($originalProduct->status);
                }

                if (!empty($item->actions) && ($quantity - $exceptedQuantity) >= 0) {
                    foreach ($item->actions as $action) {
                        abort_if(
                            ($action['id'] == OrderProductAction::Cancel->value && $originalProduct->status != OrderProductStatus::Preparing)
                            || $action['id'] == OrderProductAction::Refund->value && !in_array($originalProduct->status, [OrderProductStatus::Served, OrderProductStatus::Ready]),
                            400,
                            __("core::errors.an_unexpected_error_occurred")
                        );
                    }

                    if ($quantity === $exceptedQuantity) {
                        $item->orderProduct->setStatus(
                            OrderProductAction::from(array_values($item->actions)[0]['id'])->getProductStatus()
                        );
                        $item->qty = $exceptedQuantity;
                        $data->push($item);
                    } else {
                        $data->push($item);
                        $item->orderProduct->setId();
                        foreach ($item->actions as $action) {
                            $newItem = clone $item;
                            $newItem->qty = $action['quantity'];
                            $newItem->orderProduct->setStatus(
                                OrderProductAction::from($action['id'])->getProductStatus()
                            );
                            $data->push($newItem);
                        }
                    }
                } else {
                    $data->push($item);
                }
            } else {
                $data->push($item);
            }
        }

        return $data;
    }
}
