<?php

namespace Modules\Order\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Modules\Branch\Traits\HasBranch;
use Modules\Cart\CartDiscount;
use Modules\Cart\CartItem;
use Modules\Cart\CartTax;
use Modules\Currency\Currency;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\GiftCard\Services\GiftCard\GiftCardServiceInterface;
use Modules\GiftCard\Services\GiftCardTransaction\GiftCardTransactionServiceInterface;
use Modules\Invoice\Enums\InvoiceKind;
use Modules\Invoice\Models\Invoice;
use Modules\Loyalty\Services\LoyaltyGift\LoyaltyGiftServiceInterface;
use Modules\Order\Enums\DiscountType;
use Modules\Order\Enums\OrderPaymentStatus;
use Modules\Order\Enums\OrderProductStatus;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\OrderType;
use Modules\Order\Events\OrderPaid;
use Modules\Order\Events\OrderUpdateStatus;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Payment\Enums\PaymentType;
use Modules\Payment\Models\Payment;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosSession;
use Modules\Pos\Services\PosCashMovement\PosCashMovementServiceInterface;
use Modules\SeatingPlan\Models\Table;
use Modules\SeatingPlan\Models\TableMerge;
use Modules\Support\Eloquent\Model;
use Modules\Support\Enums\PriceType;
use Modules\Support\Money;
use Modules\Support\Traits\HasCreatedBy;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasSortBy;
use Modules\Tax\Enums\TaxType;
use Modules\User\Models\User;
use Modules\Voucher\Models\Voucher;
use App\Traits\HasPaseUuid;
use Throwable;

/**
 * @property int $id
 * @property int|null $table_id
 * @property-read Table|null $table
 * @property int|null $waiter_id
 * @property-read User|null $waiter
 * @property int|null $cashier_id
 * @property-read User|null $cashier
 * @property int|null $pos_register_id
 * @property-read PosRegister|null $posRegister
 * @property int|null $table_merge_id
 * @property-read TableMerge|null $tableMerge
 * @property int|null $merged_into_order_id
 * @property-read Order|null $mergedIntoOrder
 * @property int|null $customer_id
 * @property-read User|null $customer
 * @property int|null $merged_by
 * @property-read PosRegister|null $mergedBy
 * @property-read Collection<OrderTax> $taxes
 * @property-read Collection<OrderProduct> $products
 * @property int|null $pos_session_id
 * @property-read PosSession|null $posSession
 * @property string $reference_no
 * @property string $order_number
 * @property-read  OrderStatus|null $previous_status
 * @property OrderStatus $status
 * @property-read  OrderStatus|null $next_status
 * @property OrderType $type
 * @property OrderPaymentStatus $payment_status
 * @property-read OrderDiscount $discount
 * @property-read Collection|Invoice[] $invoices
 * @property-read Collection|Invoice[] $mergedInvoices
 * @property-read Collection|Invoice[] $all_invoices
 *
 * @property string $currency
 * @property float $currency_rate
 * @property Money $subtotal
 * @property Money $total
 * @property Money $due_amount
 * @property Money $cost_price
 * @property Money $revenue
 * @property int $guest_count
 * @property string|null $notes
 * @property string|null $car_plate
 * @property string|null $car_description
 * @property Carbon $order_date
 * @property boolean $is_stock_deducted
 * @property int|null $modified_by
 * @property User|null $modifiedBy
 * @property-read Collection|Payment[] $payments
 * @property Carbon|null $modified_at
 * @property Carbon|null $served_at
 * @property Carbon|null $closed_at
 * @property Carbon|null $merged_at
 * @property Carbon|null $payment_at
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|static|self activeOrders()
 * @method static Builder|static|self forMerge()
 * @method static Builder|static|self completed()
 * @method static Builder|static|self withoutCanceledOrders()
 * @method static Builder|static|self visibleForKitchen()
 * @method static Builder|static|self forKitchenCategories(array $categoryIds)
 */
class Order extends Model
{
    use HasCreatedBy,
        HasFilters,
        HasSortBy,
        HasBranch,
        HasPaseUuid;

    /**
     * Default date column
     *
     * @var string
     */
    public static string $defaultDateColumn = 'created_at';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get total sales (converted to default currency if needed)
     *
     * @param string|null $currency
     * @return Money
     */
    public static function totalSales(?string $currency = null): Money
    {
        if (auth()->check() && auth()->user()->assignedToBranch()) {
            $total = self::withoutCanceledOrders()->sum('total');
        } else {
            $total = self::withoutCanceledOrders()
                ->selectRaw('SUM(total * COALESCE(currency_rate, 1)) AS total_sales')
                ->value('total_sales') ?? 0;
        }

        return is_null($currency) ? Money::inDefaultCurrency($total) : new Money($total, $currency);
    }

    /**
     * Get average order value (converted to default currency if needed)
     *
     * @param string|null $currency
     * @return Money
     */
    public static function averageOrderValue(?string $currency = null): Money
    {
        if (auth()->check() && auth()->user()->assignedToBranch()) {
            $avg = self::withoutCanceledOrders()->average('total') ?? 0;
        } else {
            $avg = self::withoutCanceledOrders()
                ->selectRaw('AVG(total * COALESCE(currency_rate, 1)) AS avg_order')
                ->value('avg_order') ?? 0;
        }

        return is_null($currency) ? Money::inDefaultCurrency($avg) : new Money($avg, $currency);
    }

    /**
     * Boot the model and handle stock adjustments on create, update, and delete.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->reference_no = static::generateReferenceNo();
            $order->order_number = static::generateOrderNumber($order->branch_id);
        });
    }

    /**
     * Generate reference no
     *
     * @return string
     */
    public static function generateReferenceNo(): string
    {
        return mb_strtoupper("ORD-" . substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 10));
    }

    /**
     * Generate order number
     *
     * @param int $branchId
     * @return string
     */
    public static function generateOrderNumber(int $branchId): string
    {
        // lockForUpdate sobre el indice (order_date, order_number, branch_id)
        // serializa el calculo + insert dentro de la transaccion exterior,
        // evitando que dos ordenes concurrentes calculen el mismo numero.
        // MAX(CAST AS UNSIGNED) porque order_number es string con padding
        // ('01'..'99','100') — comparacion lexicografica daria '99' > '100'.
        $lastNumber = (int) Order::where('branch_id', $branchId)
            ->whereDate('order_date', today())
            ->lockForUpdate()
            ->max(DB::raw('CAST(order_number AS UNSIGNED)'));

        $next = $lastNumber + 1;
        return str_pad((string) $next, $next < 100 ? 2 : 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get a model modified by
     *
     * @return BelongsTo
     */
    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "modified_by")
            ->withOutGlobalBranchPermission()
            ->withoutGlobalActive()
            ->withTrashed();
    }

    /**
     * Get a waiter
     *
     * @return BelongsTo
     */
    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, "waiter_id")
            ->withOutGlobalBranchPermission()
            ->withoutGlobalActive()
            ->withTrashed();
    }

    /**
     * Get cashier
     *
     * @return BelongsTo
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, "cashier_id")
            ->withOutGlobalBranchPermission()
            ->withoutGlobalActive()
            ->withTrashed();
    }

    /**
     * Get customer
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, "customer_id")
            ->withoutGlobalActive()
            ->withTrashed()
            ->withDefault([
                'id' => null,
                'name' => User::walkInName(),
            ]);
    }


    /**
     * Get customer name
     * @return string|null
     */
    public function getCustomerName(): ?string
    {
        return $this->relationLoaded("customer") ? ($this->customer?->name ?: User::walkInName()) : null;
    }

    /** @inheritDoc */
    public function allowedFilterKeys(): array
    {
        return [
            "search",
            "status",
            "payment_status",
            "type",
            "from",
            "to",
            "group_by_date",
            "customer_id",
            self::BRANCH_COLUMN_NAME
        ];
    }

    /**
     * Scope a query to search across all fields.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeSearch(Builder $query, string $value): void
    {
        $query->where(function (Builder $query) use ($value) {
            $query->like('reference_no', $value)->orLike('order_number', $value);
        });
    }

    /**
     * Scope a query to get all active orders.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActiveOrders(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query
                ->where(function (Builder $query) {
                    $query->whereNot('payment_status', OrderPaymentStatus::Paid)
                        ->orWhereIn('status', [
                            OrderStatus::Pending,
                            OrderStatus::Confirmed,
                            OrderStatus::Preparing,
                            OrderStatus::Ready,
                            OrderStatus::Served,
                        ]);
                })
                ->whereNotIn('status', [
                    OrderStatus::Cancelled,
                    OrderStatus::Refunded,
                    OrderStatus::Merged,
                ]);
        });
    }

    /**
     * Scope a query to get all orders by type.
     *
     * @param Builder $query
     * @param string $type
     * @return void
     */
    public function scopeType(Builder $query, string $type): void
    {
        $query->where("orders.type", $type);
    }

    /**
     * Scope a query to get all orders completed.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where("status", OrderStatus::Completed);
    }

    /**
     * Scope a query to get all without canceled orders.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeWithoutCanceledOrders(Builder $query): void
    {
        $query->whereNotIn("status", [OrderStatus::Cancelled, OrderStatus::Refunded, OrderStatus::Merged]);
    }

    /**
     * Scope a query to get all active orders.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeForMerge(Builder $query): void
    {
        $query->where(function ($query) {
            $query
                ->where('type', OrderType::DineIn)
                ->whereNot('payment_status', OrderPaymentStatus::Paid)
                ->whereIn('status', [
                    OrderStatus::Pending,
                    OrderStatus::Confirmed,
                    OrderStatus::Preparing,
                    OrderStatus::Ready,
                    OrderStatus::Served,
                ]);
        });
    }

    /**
     * Get due amount
     *
     * @return Attribute
     */
    public function dueAmount(): Attribute
    {
        return Attribute::get(fn($amount) => isset($this->currency) ? new Money($amount, $this->currency) : Money::inDefaultCurrency($amount));
    }

    /**
     * Get cost price
     *
     * @return Attribute
     */
    public function costPrice(): Attribute
    {
        return Attribute::get(fn($amount) => isset($this->currency) ? new Money($amount, $this->currency) : Money::inDefaultCurrency($amount));
    }

    /**
     * Get revenue
     *
     * @return Attribute
     */
    public function revenue(): Attribute
    {
        return Attribute::get(fn($amount) => isset($this->currency) ? new Money($amount, $this->currency) : Money::inDefaultCurrency($amount));
    }

    /**
     * Get total taxes
     *
     * @return Money
     */
    public function totalTax(): Money
    {
        $total = 0;

        if ($this->hasTax()) {
            ($this->relationLoaded("taxes") ? $this->taxes : $this->taxes()->get())
                ->each(function ($tax) use (&$total) {
                    $total += $tax->amount->amount();
                });
        }

        return isset($this->currency)
            ? new Money($total, $this->currency)
            : Money::inDefaultCurrency($total);
    }

    /**
     * Determine if order has tax or not
     *
     * @return bool
     */
    public function hasTax(): bool
    {
        return $this->taxes->isNotEmpty();
    }

    /**
     * Get order taxes
     *
     * @return HasMany
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(OrderTax::class)
            ->whereNull("order_product_id");
    }

    /**
     * Determine if order has a discount or not
     *
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return !is_null($this->discount);
    }

    /**
     * Store product
     *
     * @param CartItem $cartItem
     * @return void
     * @throws Throwable
     */
    public function updateOrCreateProduct(CartItem $cartItem): void
    {
        $giftService = app(LoyaltyGiftServiceInterface::class);

        $params = [
            'product_id' => $cartItem->product->id,
            'currency' => $this->currency,
            'loyalty_gift_id' => $cartItem->loyaltyGift?->id(),
            'currency_rate' => $this->currency_rate,
            'quantity' => $cartItem->qty ?: 1,
            'unit_price' => $cartItem->unitPrice(),
            'subtotal' => $cartItem->subtotal(),
            'tax_total' => $cartItem->taxTotal(),
            'total' => $cartItem->total()
        ];

        if (in_array($cartItem->orderProduct?->status(), [OrderProductStatus::Cancelled, OrderProductStatus::Refunded])) {
            $params['status'] = $cartItem->orderProduct->status();
        }

        if (!is_null($cartItem->orderProduct?->id())) {
            /** @var OrderProduct $orderProduct */
            $orderProduct = $this->products()
                ->where('id', $cartItem->orderProduct->id())
                ->first();
            $orderProduct->update($params);
        } else {
            /** @var OrderProduct $orderProduct */
            $orderProduct = $this->products()->create($params);
            $orderProduct->storeOptions($cartItem->options);
            if (!is_null($orderProduct->loyalty_gift_id)) {
                $giftService->useGift(loyaltyGiftId: $orderProduct->loyalty_gift_id, order: $this);
            }
        }

        $orderProduct->updateOrCreateTaxes($cartItem->taxes());
    }

    /**
     * Get subtotal
     *
     * @return Attribute
     */
    public function subtotal(): Attribute
    {
        return Attribute::get(fn($amount) => isset($this->currency) ? new Money($amount, $this->currency) : Money::inDefaultCurrency($amount));
    }

    /**
     * Get total
     *
     * @return Attribute
     */
    public function total(): Attribute
    {
        return Attribute::get(fn($amount) => isset($this->currency) ? new Money($amount, $this->currency) : Money::inDefaultCurrency($amount));
    }

    /**
     * Get products
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    /**
     * Update Or Create Taxes
     *
     * @param Collection $taxes
     * @return void
     */
    public function updateOrCreateTaxes(Collection $taxes): void
    {
        $this->taxes()
            ->whereNotIn("tax_id", $taxes->map(fn(CartTax $tax) => $tax->id()))
            ->delete();

        foreach ($taxes as $tax) {
            $this->updateOrCreateTax($tax);
        }
    }

    /**
     * Update Or Create Tax
     *
     * @param CartTax $tax
     * @return void
     */
    public function updateOrCreateTax(CartTax $tax): void
    {
        $this->taxes()
            ->updateOrCreate(
                ['tax_id' => $tax->id()],
                [
                    'name' => $tax->translationsName(),
                    'rate' => $tax->rate(),
                    'currency' => $tax->currency(),
                    'currency_rate' => $this->currency_rate,
                    'amount' => $tax->amount()->amount(),
                    'type' => $tax->type(),
                    'compound' => $tax->compound(),
                ]
            );
    }


    /**
     * Get pos register
     *
     * @return BelongsTo
     */
    public function posRegister(): BelongsTo
    {
        return $this->belongsTo(PosRegister::class)
            ->withOutGlobalBranchPermission()
            ->withoutGlobalActive()
            ->withTrashed();
    }

    /**
     * Get pos session
     *
     * @return BelongsTo
     */
    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class)
            ->withOutGlobalBranchPermission()
            ->withTrashed();
    }

    /**
     * Determine if order allow cancel
     *
     * @return bool
     */
    public function cancelIsAllowed(): bool
    {
        return $this->payment_status->isUnpaid()
            && !in_array($this->status, [OrderStatus::Cancelled, OrderStatus::Merged, OrderStatus::Refunded]);
    }

    /**
     * Determine if order allow refund
     *
     * @return bool
     */
    public function refundIsAllowed(): bool
    {
        return !$this->payment_status->isUnpaid()
            && !in_array($this->status, [OrderStatus::Cancelled, OrderStatus::Refunded, OrderStatus::Merged])
            && ($this->status == OrderStatus::Pending || $this->created_at->diffInHours(now()) < 24);
    }

    /**
     * Determine allow update order status
     *
     * @return bool
     */
    public function allowUpdateStatus(): bool
    {
        return !(is_null($this->next_status)
            || ($this->next_status != OrderStatus::Confirmed && !$this->isScheduledForToday())
            || (!$this->payment_status->isPaid() && $this->next_status == OrderStatus::Completed));
    }

    /**
     * Check if the order is scheduled for today (ignores time of day).
     *
     * @return bool
     */
    public function isScheduledForToday(): bool
    {
        if ($this->scheduled_at) {
            return $this->scheduled_at->toDateString() <= now()->toDateString();
        }

        return true;
    }

    /**
     * Store status log
     *
     * @param OrderStatus $status
     * @param int|null $changedById
     * @param int|null $reasonId
     * @param string|null $note
     * @return OrderStatusLog
     */
    public function storeStatusLog(
        OrderStatus $status,
        ?int        $changedById = null,
        ?int        $reasonId = null,
        ?string     $note = null,
    ): OrderStatusLog
    {
        return $this->statusLogs()
            ->create([
                "changed_by" => $changedById,
                "reason_id" => $reasonId,
                "status" => $status,
                "note" => $note
            ]);
    }

    /**
     * Get order status logs
     *
     * @return HasMany
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy("id", "desc");
    }

    /**
     * Determine if order allow to add payment
     *
     * @return bool
     */
    public function allowAddPayment(): bool
    {
        return !$this->payment_status->isPaid();
    }

    /**
     * Get next status
     *
     * @return Attribute
     */
    public function nextStatus(): Attribute
    {
        return Attribute::get(
            fn() => match ($this->status) {
                OrderStatus::Pending => OrderStatus::Confirmed,
                OrderStatus::Confirmed => OrderStatus::Preparing,
                OrderStatus::Preparing => OrderStatus::Ready,
                OrderStatus::Ready => $this->type === OrderType::DineIn ? OrderStatus::Served : OrderStatus::Completed,
                OrderStatus::Served => OrderStatus::Completed,
                default => null
            }
        );
    }

    /**
     * Get previous status
     *
     * @return Attribute
     */
    public function previousStatus(): Attribute
    {
        return Attribute::get(
            fn() => match ($this->status) {
                OrderStatus::Confirmed => OrderStatus::Pending,
                OrderStatus::Preparing => OrderStatus::Confirmed,
                OrderStatus::Ready => OrderStatus::Preparing,
                OrderStatus::Served => OrderStatus::Ready,
                OrderStatus::Completed => $this->type === OrderType::DineIn ? OrderStatus::Served : OrderStatus::Ready,
                default => null
            }
        );
    }

    /**
     * Get table merge
     *
     * @return BelongsTo
     */
    public function tableMerge(): BelongsTo
    {
        return $this->belongsTo(TableMerge::class)
            ->withOutGlobalBranchPermission()
            ->withTrashed();
    }

    /**
     * Get merged into order
     *
     * @return BelongsTo
     */
    public function mergedIntoOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class)
            ->withOutGlobalBranchPermission();
    }

    /**
     * Get merged by
     *
     * @return BelongsTo
     */
    public function mergedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "merged_by")
            ->withOutGlobalBranchPermission()
            ->withoutGlobalActive()
            ->withTrashed();
    }

    /**
     * Recalculate Order
     *
     * @param bool $deleteTaxesDuplicates
     * @return void
     */
    public function recalculate(bool $deleteTaxesDuplicates = false): void
    {
        if ($deleteTaxesDuplicates) {
            $this->deleteTaxesDuplicates();
        }

        $this->load(['taxes', 'products.product.categories', 'discount.discountable']);

        $subtotal = 0;
        $costPrice = 0;
        $revenue = 0;

        foreach ($this->products as $product) {
            $subtotal += $product->total->amount();
            $costPrice += $product->cost_price->amount();
            $revenue += $product->revenue->amount();
        }

        $totalTaxes = $this->recalculateTaxes($subtotal);

        $discountAmount = 0;
        $precision = Currency::subunit($this->currency);

        if (!is_null($this->discount) && $this->discount->discountable) {
            $discountable = $this->discount->discountable;

            $applicableProducts = $this->products->filter(function ($product) use ($discountable) {
                $productSkus = collect($discountable->conditions['products'] ?? []);
                $categories = collect($discountable->conditions['categories'] ?? []);

                $skuMatch = $productSkus->isEmpty() || $productSkus->contains($product->product->sku);
                $categoryMatch = $categories->isEmpty() || $categories->intersect($product->product->categories->pluck('slug'))->isNotEmpty();

                return $skuMatch && $categoryMatch;
            });

            $applicableSubtotal = $applicableProducts->sum(fn($product) => $product->total->amount());

            if ($discountable->type === PriceType::Percent) {
                $discountAmount = $applicableSubtotal * ($discountable->value / 100);
            } elseif ($discountable->type === PriceType::Fixed) {
                $discountAmount = min($discountable->value, $applicableSubtotal);
            }

            if (!is_null($discountable->max_discount) && $discountAmount > $discountable->max_discount->amount()) {
                $discountAmount = $discountable->max_discount->amount();
            }

            $this->discount->update(['amount' => round($discountAmount, $precision)]);
        }

        $totalAfterDiscount = ($subtotal - $discountAmount) + $totalTaxes;

        $this->update([
            'subtotal' => round($subtotal, $precision),
            'total' => round($totalAfterDiscount, $precision),
            'cost_price' => round($costPrice, $precision),
            'revenue' => round($revenue, $precision),
        ]);

        $this->refreshDueAmount();
    }

    /**
     * Delete duplicate taxes by tax_id across given orders.
     *
     * Keeps the first record (lowest id) for each tax_id
     * and deletes the rest.
     *
     * @return int number of deleted rows
     */
    public function deleteTaxesDuplicates(): int
    {
        return DB::table('order_taxes as t1')
            ->join(
                'order_taxes as t2',
                fn($join) => $join->on('t1.tax_id', '=', 't2.tax_id')
                    ->on('t1.id', '>', 't2.id')
                    ->on('t1.order_id', '=', 't2.order_id')
            )
            ->where('t1.order_id', $this->id)
            ->whereNull('t1.order_product_id')
            ->delete();
    }

    /**
     * Get order table
     *
     * @return BelongsTo
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class)
            ->withOutGlobalBranchPermission()
            ->withoutGlobalActive()
            ->withTrashed();
    }

    /**
     * Recalculate order taxes
     *
     * @param float $basePrice
     * @return float
     */
    public function recalculateTaxes(float $basePrice): float
    {
        $totalTaxes = 0;

        foreach ($this->taxes as $tax) {
            $taxAmount = 0;

            if ($tax->type === TaxType::Exclusive) {
                $taxAmount = $basePrice * ($tax->rate / 100);
            }

            $totalTaxes += $taxAmount;

            if ($tax->compound) {
                $basePrice += $taxAmount;
            }

            $tax->update(["amount" => $taxAmount]);
        }

        return $totalTaxes;
    }

    /**
     * Refresh due amount
     *
     * @return void
     */
    public function refreshDueAmount(): void
    {
        $scale = Currency::subunit($this->currency);
        $factor = 10 ** $scale;

        $totalPaymentsMinor = (int)round(
            $this->payments()->where('type', PaymentType::Payment)->sum('amount') * $factor
        );

        $totalRefundsMinor = (int)round(
            $this->payments()->where('type', PaymentType::Refund)->sum('amount') * $factor
        );

        $paidMinor = $totalPaymentsMinor - $totalRefundsMinor;

        $totalRounded = round($this->total->amount(), $scale);
        $totalMinor = (int)round($totalRounded * $factor);

        $dueMinor = max($totalMinor - $paidMinor, 0);

        if ($paidMinor <= 0) {
            $paymentStatus = OrderPaymentStatus::Unpaid;
        } elseif ($paidMinor < $totalMinor) {
            $paymentStatus = OrderPaymentStatus::PartiallyPaid;
        } else {
            $paymentStatus = OrderPaymentStatus::Paid;
        }

        $this->updateQuietly([
            'due_amount' => $dueMinor / $factor,
            'payment_status' => $paymentStatus,
            'payment_at' => $paymentStatus == OrderPaymentStatus::Paid ? now() : null,
        ]);

        if ($paymentStatus == OrderPaymentStatus::Paid && is_null($this->table_merge_id)) {
            event(new OrderPaid($this));
        }
    }

    /**
     * Get order payments
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)
            ->withOutGlobalBranchPermission()
            ->withTrashed();
    }

    /**
     * Determine if order have refund amount
     *
     * @return bool
     */
    public function hasRefundAmount(): bool
    {
        return $this->getRefundedAmount()->amount() > 0 && !$this->payment_status->isUnpaid();
    }

    /**
     * Get refunded amount
     *
     * @return Money
     */
    public function getRefundedAmount(): Money
    {
        return $this->total->subtract($this->due_amount);
    }

    /**
     * Determine allow edit order or not
     *
     * @return bool
     */
    public function editIsAllowed(): bool
    {
        return !$this->payment_status->isPaid()
            && in_array(
                $this->status,
                [
                    OrderStatus::Pending,
                    OrderStatus::Confirmed,
                    OrderStatus::Preparing,
                    OrderStatus::Ready,
                    OrderStatus::Served,
                ]
            );
    }

    /**
     * Guard centralizado para writes sobre la orden. Tirar 422 si la
     * orden esta en estado terminal (paid/refunded/cancelled/merged).
     * El reopen real vive como flujo aparte via refund/credit note —
     * no re-habilita updates libres.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function ensureEditable(): void
    {
        abort_unless(
            $this->editIsAllowed(),
            422,
            __('order::messages.edit_not_allowed'),
        );
    }

    /**
     * Update Or Create Discount
     *
     * @param CartDiscount|null $discount
     * @return void
     * @throws Throwable
     */
    public function updateOrCreateDiscount(?CartDiscount $discount): void
    {
        /** @var OrderDiscount|null $orderDiscount */
        $orderDiscount = $this->discount;
        $oldLoyaltyGiftId = $orderDiscount?->loyalty_gift_id;

        $amount = $discount?->value();

        if (is_null($amount) || $amount->amount() == 0) {
            $discount = null;
        }

        $giftService = app(LoyaltyGiftServiceInterface::class);

        if (!is_null($oldLoyaltyGiftId) && $oldLoyaltyGiftId != $discount?->loyaltyGift()?->id()) {
            $giftService->rollbackGift(loyaltyGiftId: $oldLoyaltyGiftId, order: $this);
        }

        if ((is_null($discount) || $amount->isZero()) && $orderDiscount) {
            $orderDiscount->discountable?->unusedOnce();
            $orderDiscount->delete();
            return;
        }

        if (is_null($discount)) {
            return;
        }

        $discountModel = $discount->model();

        $data = [
            'discountable_type' => $discountModel->getMorphClass(),
            'discountable_id' => $discountModel->id,
            'type' => $discountModel instanceof Voucher
                ? DiscountType::Voucher
                : DiscountType::Discount,
            'currency' => $this->currency,
            'currency_rate' => $this->currency_rate,
            'loyalty_gift_id' => $discount->loyaltyGift()?->id(),
            'amount' => $amount->amount(),
        ];

        if ($orderDiscount) {
            $oldType = $orderDiscount->discountable_type;
            $oldId = $orderDiscount->discountable_id;

            $orderDiscount->update($data);

            if ($oldType !== $data['discountable_type'] || $oldId !== $data['discountable_id']) {
                $orderDiscount->discountable?->unusedOnce();
                $discountModel->usedOnce();
            }

            return;
        }


        /** @var OrderDiscount $orderDiscount */
        $orderDiscount = $this->discount()->create($data);
        $discountModel->usedOnce();

        if (!is_null($orderDiscount->loyalty_gift_id) && $oldLoyaltyGiftId != $orderDiscount->loyalty_gift_id) {
            $giftService->useGift(loyaltyGiftId: $orderDiscount->loyalty_gift_id, order: $this);
        }
    }

    /**
     * Get discount
     *
     * @return HasOne<OrderDiscount,static>
     */
    public function discount(): HasOne
    {
        return $this->hasOne(OrderDiscount::class);
    }

    /**
     * Revert the order status back to "Preparing" if the order
     * is not in Pending or Confirmed status but still contains
     * products with a Pending status.
     *
     * This typically happens when an already-progressed order
     * gets modified (e.g., items added/updated), requiring the status
     * to roll back to Preparing.
     *
     * @return void
     */
    public function revertOrderStatusToPreparingIfModified(): void
    {
        if (
            !in_array($this->status, [OrderStatus::Pending, OrderStatus::Confirmed])
            && $this->products()->where('status', OrderProductStatus::Pending)->exists()
        ) {
            $this->update([
                'status' => OrderStatus::Preparing,
            ]);

            event(new OrderUpdateStatus(
                order: $this,
                status: OrderStatus::Preparing,
                note: "Order was modified. Status changed back to Preparing.",
            ));
        }
    }

    /**
     * Handle overpayment logic when an order is modified.
     *
     * If the order has an overpaid amount, a refund payment is created.
     * Otherwise, the order due amount is recalculated.
     *
     * @param array $data
     *     Expected keys:
     *     - refund_payment_method
     *     - session_id
     *     - currency_rate
     *
     * @return void
     * @throws Throwable
     */
    public function handleOverpaymentAdjustment(array $data): void
    {
        if ($data['overpaid_amount'] > 0) {
            $this->storePayment([
                'cashier_id' => auth()->id(),
                'method' => $data['refund_payment_method'],
                'amount' => $data['overpaid_amount'],
                'meta' => ['reason' => 'Order changed and overpaid'],
                'session' => PosSession::findOrFail($data['session_id']),
                'type' => PaymentType::Refund->value,
                'currency_rate' => $data['currency_rate'],
                'notes' => 'Order changed and overpaid',
            ]);
        }
        $this->refreshDueAmount();
    }

    /**
     * Store payment
     *
     * @param array $data
     * @return void
     * @throws Throwable
     */
    public function storePayment(array $data): void
    {
        $data = $this->resolveGiftCardPaymentMeta($data);

        /** @var Payment $payment */
        $payment = $this->payments()->create([
            "order_reference_no" => $this->reference_no,
            'branch_id' => $this->branch_id,
            'cashier_id' => $data['cashier_id'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null,
            'method' => $data['method'],
            'amount' => $data['amount'],
            'type' => $data['type'] ?? PaymentType::Payment,
            'currency' => $this->currency,
            'currency_rate' => $data['currency_rate'] ?? $this->currency_rate,
            'meta' => $data['meta'] ?? null,
        ]);

        $this->handleGiftCardPayment($payment, $data);

        if ($data['method'] == PaymentMethod::Cash->value) {
            if ($payment->type == PaymentType::Refund) {
                app(PosCashMovementServiceInterface::class)
                    ->refundCash(
                        session: $data['session'],
                        amount: abs($data['amount']),
                        orderId: $this->id,
                        paymentId: $payment->id,
                        reference: $this->reference_no,
                        notes: $data['notes'] ?? null,
                    );
            } else {
                app(PosCashMovementServiceInterface::class)
                    ->sale(
                        session: $data['session'],
                        amount: abs($data['amount']),
                        orderId: $this->id,
                        paymentId: $payment->id,
                    );
            }
        }
    }

    /**
     * Resolve and normalize gift card metadata before persisting the payment row.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function resolveGiftCardPaymentMeta(array $data): array
    {
        if (($data['method'] ?? null) !== PaymentMethod::GiftCard->value || empty($data['meta']['gift_card_code'])) {
            return $data;
        }

        $giftCard = app(GiftCardServiceInterface::class)->findOrFail($data['meta']['gift_card_code']);

        abort_if(
            $giftCard->customer_id && $giftCard->customer_id !== $this->customer_id,
            422,
            __('giftcard::messages.card_customer_mismatch')
        );

        return [
            ...$data,
            'meta' => [
                ...($data['meta'] ?? []),
                'gift_card_code' => $giftCard->code,
                'gift_card_id' => $giftCard->id,
            ],
        ];
    }

    /**
     * Handle gift card side effects after the payment row has been created.
     *
     * @throws Throwable
     */
    protected function handleGiftCardPayment(Payment $payment, array $data): void
    {
        if ($payment->method !== PaymentMethod::GiftCard || $payment->type !== PaymentType::Refund) {
            return;
        }

        $giftCardCode = $payment->meta['gift_card_code'] ?? null;
        if (blank($giftCardCode)) {
            return;
        }

        $giftCard = app(GiftCardServiceInterface::class)->findOrFail($giftCardCode);
        $refundAmount = app(GiftCardServiceInterface::class)->convertOrderAmountToGiftCardAmount(
            giftCard: $giftCard,
            amount: $payment->amount->amount(),
            orderCurrency: $this->currency,
            orderCurrencyRate: $payment->currency_rate,
        );

        app(GiftCardTransactionServiceInterface::class)
            ->record($giftCard, [
                'type' => GiftCardTransactionType::Refund,
                'amount' => $refundAmount->amount(),
                'amount_in_order_currency' => $payment->amount->amount(),
                'order_currency' => $this->currency,
                'exchange_rate' => $payment->currency_rate,
                'order_id' => $this->id,
                'branch_id' => $this->branch_id,
                'notes' => $data['notes'] ?? null,
            ]);
    }

    /**
     * Store payments
     *
     * @param array $payments
     * @return void
     * @throws Throwable
     */
    public function storePayments(array $payments): void
    {
        foreach ($payments as $payment) {
            $this->storePayment($payment);
        }

        $this->refreshDueAmount();
    }

    /**
     * Get invoices
     *
     * @return HasMany
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getInvoice($with = []): ?Invoice
    {
        return Invoice::with($with)
            ->where('invoice_kind', InvoiceKind::Standard)
            ->when(
                is_null($this->table_merge_id),
                fn($query) => $query->where('order_id', $this->id)
            )
            ->when(
                !is_null($this->table_merge_id),
                fn($query) => $query->where('table_merge_id', $this->table_merge_id)
            )
            ->first();
    }

    /**
     * Get merged invoices
     *
     * @return HasMany
     */
    public function mergedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'table_merge_id');
    }

    public function scopeVisibleForKitchen(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereNotIn('status', [
                OrderStatus::Ready,
                OrderStatus::Served,
            ])
                ->orWhereHas('statusLogs', function ($log) {
                    $log->whereIn('status', [
                        OrderStatus::Ready,
                        OrderStatus::Served,
                    ])
                        ->where('created_at', '>=', now()->subMinutes(10));
                });
        });
    }

    /**
     * For kitchen categories
     * @param Builder $query
     * @param array $categoryIds
     * @return void
     */
    public function scopeForKitchenCategories(Builder $query, array $categoryIds): void
    {
        if (!empty($categoryIds)) {
            $query->whereHas('products', function ($q) use ($categoryIds) {
                $q->whereHas('product.categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            });
        }
    }

    /**
     * Re-Calculate Order Status
     * @return void
     */
    public function recalculateOrderStatus(): void
    {
        $statuses = $this->products()
            ->whereNotIn("status", [
                OrderProductStatus::Cancelled,
                OrderProductStatus::Refunded,
            ])
            ->pluck('status')
            ->unique();

        $newStatus = match (true) {
            $statuses->contains(OrderProductStatus::Pending)
            || $statuses->contains(OrderProductStatus::Preparing)
            => OrderStatus::Preparing,
            $statuses->every(fn($s) => $s === OrderProductStatus::Served) => OrderStatus::Served,
            $statuses->contains(OrderProductStatus::Ready) => OrderStatus::Ready,
            default => OrderStatus::Confirmed,
        };

        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);

            event(new OrderUpdateStatus(
                order: $this,
                status: $newStatus,
                changedById: auth()->id(),
                stopUpdateOrderProductStatus: true
            ));
        }
    }

    /**
     * All distinct invoices using Laravel 12 Attribute Cast.
     */
    protected function allInvoices(): Attribute
    {
        return Attribute::get(function () {
            $invoices = $this->relationLoaded('invoices')
                ? $this->getRelation('invoices')
                : collect();

            $merged = $this->relationLoaded('mergedInvoices')
                ? $this->getRelation('mergedInvoices')
                : collect();

            return $invoices
                ->merge($merged)
                ->unique('id')
                ->values();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "status" => OrderStatus::class,
            "type" => OrderType::class,
            "payment_status" => OrderPaymentStatus::class,
            "order_date" => "date",
            "served_at" => "datetime",
            "closed_at" => "datetime",
            "merged_at" => "datetime",
            "payment_at" => "datetime",
            "modified_at" => "datetime",
            "is_stock_deducted" => "boolean",
            "start_date" => "datetime",
            "end_date" => "datetime",
            "scheduled_at" => "datetime",
            "bill_requested_at" => "datetime",
            "paused_at" => "datetime",
        ];
    }

    /** @inheritDoc */
    protected function getSortableAttributes(): array
    {
        return [
            "reference_no",
            "order_number",
            "status",
            "type",
            "total",
            "payment_status",
            "customer_id",
            self::BRANCH_COLUMN_NAME,
        ];
    }

}
