<?php

namespace Modules\Order\Services\Order;

use App\Forkiva;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Modules\Branch\Models\Branch;
use Modules\Cart\Facades\Cart;
use Modules\Order\Enums\OrderPaymentStatus;
use Modules\Order\Enums\OrderProductStatus;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\OrderType;
use Modules\Order\Enums\ReasonType;
use Modules\Order\Events\OrderUpdateStatus;
use Modules\Order\Events\OrderVoided;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderProduct;
use Modules\Order\Models\Reason;
use Modules\Payment\Enums\RefundPaymentMethod;
use Modules\Pos\Models\PosRegister;
use Modules\Printer\app\Factories\PrintContentFactory;
use Modules\Printer\Enum\PrintContentType;
use Modules\Printer\Enum\PrinterPaperSize;
use Modules\Printer\Factories\PrintContents\PrintKitchenContentFactory;
use Modules\Printer\Models\PrintAgent;
use Modules\Printer\Services\Render\PrintRenderServiceInterface;
use Modules\Support\GlobalStructureFilters;

class OrderService implements OrderServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("order::orders.order");
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        $branchFilter = GlobalStructureFilters::branch();
        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            [
                "key" => 'status',
                "label" => __('order::orders.filters.status'),
                "type" => 'select',
                "options" => OrderStatus::toArrayTrans(),
            ],
            [
                "key" => 'type',
                "label" => __('order::orders.filters.type'),
                "type" => 'select',
                "options" => OrderType::toArrayTrans(),
            ],
            [
                "key" => 'payment_status',
                "label" => __('order::orders.filters.payment_status'),
                "type" => 'select',
                "options" => OrderPaymentStatus::toArrayTrans(),
            ],
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function cancel(int|string $id, array $data): void
    {
        /** @var Order $order */
        $order = $this->findOrFail($id);

        abort_unless(
            $order->cancelIsAllowed(),
            400,
            __("order::messages.order_cannot_cancel", ["status" => $order->status->trans()])
        );

        $activeSession = PosRegister::activeSession(
            $data['register_id'],
            __("admin::resource.system_cancel", ["resource" => __("order::orders.order")])
        );

        DB::transaction(function () use ($order, $data, $activeSession) {
            $order->update(['status' => OrderStatus::Cancelled]);

            event(new OrderUpdateStatus(
                order: $order,
                status: OrderStatus::Cancelled,
                reasonId: $data['reason_id'],
                changedById: auth()->id(),
                note: $data['note'] ?? null
            ));

            event(new OrderVoided(
                order: $order,
                status: OrderStatus::Cancelled,
                refundPaymentMethod: isset($data['refund_payment_method'])
                    ? RefundPaymentMethod::from($data['refund_payment_method'])
                    : null,
                posSession: $activeSession,
                note: $data['note'] ?? null,
                giftCardCode: $data['gift_card_code'] ?? null
            ));
        });
    }

    /** @inheritDoc */
    public function findOrFail(int|string $id, bool $withBranch = false): Builder|array|EloquentCollection|Order
    {
        return $this->getModel()
            ->query()
            ->when($withBranch, fn(Builder $query) => $query->with("branch"))
            ->where(fn($query) => $query->where('id', $id)
                ->orWhere('reference_no', $id))
            ->firstOrFail();
    }

    /** @inheritDoc */
    public function getModel(): Order
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return Order::class;
    }

    /** @inheritDoc */
    public function refund(int|string $id, array $data): void
    {
        /** @var Order $order */
        $order = $this->findOrFail($id);

        abort_unless($order->refundIsAllowed(), 400, __("order::messages.order_cannot_refund"));

        $activeSession = PosRegister::activeSession(
            $data['register_id'],
            __("admin::resource.system_refund", ["resource" => __("order::orders.order")])
        );

        DB::transaction(function () use ($order, $activeSession, $data) {
            $order->update(['status' => OrderStatus::Refunded]);

            event(new OrderUpdateStatus(
                order: $order,
                status: OrderStatus::Refunded,
                reasonId: $data['reason_id'],
                changedById: auth()->id(),
                note: $data['note'] ?? null
            ));

            event(new OrderVoided(
                order: $order,
                status: OrderStatus::Refunded,
                refundPaymentMethod: isset($data['refund_payment_method'])
                    ? RefundPaymentMethod::from($data['refund_payment_method'])
                    : null,
                posSession: $activeSession,
                note: $data['note'] ?? null,
                giftCardCode: $data['gift_card_code'] ?? null
            ));
        });
    }

    /** @inheritDoc */
    public function getUpdateStatusMeta(int|string $id = null): array
    {
        $order = $this->findOrFail($id, true);
        $hasRefundAmount = $order->hasRefundAmount();

        return [
            "reasons" => Reason::list(($order->cancelIsAllowed() ? ReasonType::Cancellation : ReasonType::Refund)->value),
            "pos_registers" => PosRegister::list($order->branch_id),
            "refund_payment_methods" => $hasRefundAmount
                ? array_filter(
                    RefundPaymentMethod::toArrayTrans(),
                    fn($orderType) => in_array($orderType['id'], $order->branch->payment_methods ?: [])
                ) : [],
            "order" => [
                "reference_no" => $order->reference_no,
                "refunded_amount" => $order->getRefundedAmount(),
                "is_refund" => $order->refundIsAllowed(),
                "payment_status" => $order->payment_status,
                "has_refund_amount" => $hasRefundAmount
            ]
        ];
    }

    /** @inheritDoc */
    public function moveToNextStatus(int|string $id): OrderStatus
    {
        $order = $this->findOrFail($id);
        $nextStatus = $order->next_status;

        abort_unless($order->allowUpdateStatus(), 400, __("order::messages.could_not_update_order_status"));

        return DB::transaction(function () use ($order, $nextStatus) {
            $order->update([
                "status" => $nextStatus,
                "closed_at" => ($order->type === OrderType::DineIn && $nextStatus == OrderStatus::Completed) ? now() : null
            ]);

            event(
                new OrderUpdateStatus(
                    order: $order,
                    status: $nextStatus,
                    changedById: auth()->id(),
                )
            );

            return $nextStatus;
        });
    }

    /** @inheritDoc */
    public function kitchenMoveToNextStatus(int|string $id): OrderStatus
    {
        $order = $this->findOrFail($id);
        $nextStatus = $order->next_status;

        abort_if(
            is_null($nextStatus) || !in_array($nextStatus, [OrderStatus::Preparing, OrderStatus::Ready]),
            400,
            __("order::messages.could_not_update_order_status")
        );

        return DB::transaction(function () use ($order, $nextStatus) {
            $order->update(["status" => $nextStatus]);

            event(
                new OrderUpdateStatus(
                    order: $order,
                    status: $nextStatus,
                    changedById: auth()->id(),
                )
            );

            return $nextStatus;
        });
    }

    /** @inheritDoc */
    public function upcomingOrders(?int $branchId = null, array $filters = []): Collection
    {
        $query = Order::query()
            ->when(!is_null($branchId), fn($query) => $query->where('branch_id', $branchId))
            ->whereDate("scheduled_at", ">", today())
            ->with(["products" => fn($query) => $query
                ->whereNotIn("status", [OrderProductStatus::Cancelled, OrderProductStatus::Refunded])
                ->without("taxes", "options"), "customer:id,name", "table:id,name"])
            ->activeOrders()
            ->latest();

        $this->applyPosDrawerFilters($query, $filters);

        return $query->get();
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->query()
            ->with(["branch:id,name", "customer:id,name,phone,phone_country_iso_code"])
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /** @inheritDoc */
    public function activeOrders(?int $branchId = null, array $filters = []): Collection
    {
        $query = Order::query()
            ->when(!is_null($branchId), fn($query) => $query->where('branch_id', $branchId))
            ->whereNot("type", OrderType::DineIn)
            ->where(
                fn($query) => $query->whereNull("scheduled_at")
                    ->orWhereDate("scheduled_at", "<=", today())
            )
            ->with(["products" => fn($query) => $query
                ->whereNotIn("status", [OrderProductStatus::Cancelled, OrderProductStatus::Refunded])
                ->without("taxes", "options"), "customer:id,name", "table:id,name"])
            ->activeOrders()
            ->latest();

        $this->applyPosDrawerFilters($query, $filters);

        return $query->get();
    }

    protected function applyPosDrawerFilters(Builder $query, array $filters = []): void
    {
        $search = trim((string)($filters['search'] ?? ''));
        $types = collect($filters['types'] ?? [])->filter()->values()->all();
        $statuses = collect($filters['statuses'] ?? [])->filter()->values()->all();
        $paymentStatuses = collect($filters['payment_statuses'] ?? [])->filter()->values()->all();

        if ($search !== '') {
            $query->search($search);
        }

        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        if (!empty($paymentStatuses)) {
            $query->whereIn('payment_status', $paymentStatuses);
        }
    }


    /** @inheritDoc */
    public function initEdit(int|string $id): array
    {
        $order = $this->show(
            $id,
            [
                "branch:id,name,currency",
                "customer:id,name,phone,phone_country_iso_code",
                "waiter:id,name",
                "products.gift",
                "taxes",
                "table" => fn($query) => $query
                    ->select("id", 'name', 'floor_id', 'zone_id')
                    ->with(["floor:id,name", "zone:id,name"]),
                "discount.gift",
            ]
        );

        abort_unless($order->editIsAllowed(), 400, __("order::messages.edit_not_allowed"));

        Cart::initOrder($order);

        return [
            "form" => [
                "waiter" => $order->waiter_id ? [
                    "id" => $order->waiter_id,
                    "name" => $order->waiter?->name,
                ] : null,
                "table" => $order->table ? [
                    "id" => $order->table->id,
                    "name" => $order->table->name,
                    "floor" => $order->table->floor?->name,
                    "zone" => $order->table->zone?->name,
                ] : null,
                "meta" => [
                    "notes" => $order->notes,
                    "guestCount" => $order->guest_count,
                    "carPlate" => $order->car_plate,
                    "carDescription" => $order->car_description,
                    "scheduledAt" => $order->scheduled_at?->format("Y-m-d H:i:s"),
                ],
            ],
            "refund_payment_methods" => RefundPaymentMethod::toArrayTrans(),
            "order" => [
                "id" => $order->id,
                "reference_no" => $order->reference_no,
                "status" => $order->status->toTrans(),
                "due_amount" => $order->due_amount,
                "total" => $order->total,
            ]
        ];
    }

    /** @inheritDoc */
    public function show(int|string $id, ?array $with = null): Order
    {
        return $this->getModel()
            ->query()
            ->with($with ?: [
                "branch:id,name",
                "customer:id,name,phone,phone_country_iso_code",
                "products.gift",
                "taxes",
                "payments",
                "posRegister:id,name",
                "createdBy:id,name",
                "cashier:id,name",
                "waiter:id,name",
                "table:id,name",
                "mergedIntoOrder:id,order_number,reference_no",
                "mergedBy:id,name",
                "discount.gift",
                ...(auth()->user()->can("admin.invoices.index")
                    ? [
                        "invoices:id,invoice_number,invoice_kind,total,issued_at,order_id,currency,currency_rate,uuid",
                        "mergedInvoices:id,invoice_number,invoice_kind,total,issued_at,table_merge_id,currency,currency_rate,uuid",
                    ]
                    : []),
                "statusLogs" => fn($query) => $query
                    ->with([
                        "reason:id,name",
                        "changedBy:id,name"
                    ])
            ])
            ->where(function ($query) use ($id) {
                if (is_numeric($id)) {
                    $query->where('id', $id);
                } else {
                    $query->where('reference_no', $id);
                }
            })
            ->firstOrFail();
    }

    /** @inheritDoc */
    public function previewPrint(
        int|string       $id,
        PrintContentType $type,
        ?int             $kitchenId = null,
        ?int             $specificId = null,
        string           $contentType = 'html'
    ): array
    {
        $order = $this->getModel()
            ->query()
            ->where(fn($query) => $query->where('id', $id)
                ->orWhere('reference_no', $id))
            ->when(
                $type == PrintContentType::Invoice,
                fn($query) => $query->where('payment_status', OrderPaymentStatus::Paid)
            )
            ->firstOrFail();

        $factory = PrintContentFactory::resolve($type);

        $order->load($factory->relations());
        $payload = $factory->resource($order);

        if ($type == PrintContentType::Kitchen) {
            $products = $payload["kitchens"][$kitchenId ?: (array_key_first($payload["kitchens"]) ?? 0)] ?? [];
            $payload = [...$payload, "products" => $products];
        }

        $printer = null;
        if (!is_null($specificId) || ($type == PrintContentType::Kitchen && !is_null($kitchenId))) {
            $printer = $factory->printers($type == PrintContentType::Kitchen ? $kitchenId : $specificId);
            if (is_array($printer)) {
                if (count($printer) > 0) {
                    $printer = $printer[array_key_first($printer)];
                } else {
                    $printer = null;
                }
            }
        }

        $paperSize = $printer
            ? (isset($printer->options['paper_size'])
                ? (PrinterPaperSize::tryFrom($printer->options['paper_size']) ?? PrinterPaperSize::Paper80mm)
                : PrinterPaperSize::Paper80mm)
            : PrinterPaperSize::Paper80mm;

        return [
            'content' => match ($contentType) {
                'image' => app(PrintRenderServiceInterface::class)
                    ->renderToBase64($type, $payload, $paperSize),
                'pdf' => app(PrintRenderServiceInterface::class)
                    ->renderToBase64($type, $payload, $paperSize, 'pdf'),
                default => app(PrintRenderServiceInterface::class)
                    ->renderToHtml($type, $payload, $paperSize),
            },
            "printer" => $printer ? [
                'qintrix_id' => $printer->options['qintrix_id'],
                'paper_size' => $printer->options['paper_size'] ?? PrinterPaperSize::Paper80mm->value,
            ] : null
        ];
    }

    /** @inheritDoc */
    public function printMeta(int|string $id, ?int $branchId = null, ?int $registerId = null): array
    {
        /** @var Order $order */
        $order = $this->getModel()
            ->query()
            ->with(["products.product.categories"])
            ->where(fn($query) => $query->where('id', $id)
                ->orWhere('reference_no', $id))
            ->withoutCanceledOrders()
            ->firstOrFail();


        $user = auth()->user();
        $effectiveBranchId = $user->assignedToBranch()
            ? $user->branch_id
            : (!is_null($branchId) ? $branchId : $user->effective_branch->id);

        /** @var PrintAgent $agent */
        $agent = PrintAgent::whereBranch($effectiveBranchId)
            ->withOutGlobalBranchPermission()
            ->whereNotNull('api_key')
            ->whereNotNull('host')
            ->whereNotNull('port')
            ->first();

        $data = [
            "registers" => [],
            "branches" => [],
            "contents" => [
                [
                    "type" => PrintContentType::Bill->toTrans(),
                    "label" => __("order::orders.print_actions.bill")
                ],
                ...($order->payment_status->isPaid() ? [[
                    "type" => PrintContentType::Invoice->toTrans(),
                    "label" => __("order::orders.print_actions.invoice")
                ]] : []),
                [
                    "type" => PrintContentType::Waiter->toTrans(),
                    "label" => __("order::orders.print_actions.waiter")
                ]
            ],
            "branch_id" => $effectiveBranchId,
            'agent' => $agent ? [
                'id' => $agent->id,
                'name' => $agent->name,
                'api_key' => $agent->api_key,
                'base_url' => $agent->getBaseUrl()
            ] : null
        ];

        if (is_null($registerId)) {
            $data['registers'] = PosRegister::list($effectiveBranchId);
            if (!$user->assignedToBranch() && is_null($branchId)) {
                $data['branches'] = Branch::list();
            }
        }

        if ($order->status != OrderStatus::Completed) {
            $factory = new PrintKitchenContentFactory;
            $kitchens = $factory->getKitchens($order->branch_id);
            foreach ($kitchens as $kitchen) {
                $kitchenSlugs = $kitchen->category_slugs;

                if (empty($kitchenSlugs)) {
                    $data["contents"][$kitchen->id] = [
                        "id" => $kitchen->id,
                        "label" => $kitchen->name,
                        "type" => PrintContentType::Kitchen->toTrans(),
                    ];
                    continue;
                }

                $allowedSlugs = $factory->resolveCategoryTreeSlugs($kitchenSlugs);

                $filteredProducts = $order->products
                    ->filter(function (OrderProduct $orderProduct) use ($allowedSlugs) {
                        return $orderProduct->product->categories->isEmpty()
                            || $orderProduct->product->categories
                                ->pluck('slug')
                                ->intersect($allowedSlugs)
                                ->isNotEmpty();
                    });

                if ($filteredProducts->isNotEmpty()) {
                    $data["contents"][$kitchen->id] = [
                        "id" => $kitchen->id,
                        "label" => $kitchen->name,
                        "type" => PrintContentType::Kitchen->toTrans(),
                    ];
                }
            }
            $data["contents"] = array_values($data["contents"]);
        }

        return $data;
    }
}
