<?php

namespace Modules\GiftCard\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Branch\Traits\HasBranch;
use Modules\GiftCard\Database\Factories\GiftCardTransactionFactory;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\Order\Models\Order;
use Modules\Support\Eloquent\Model;
use Modules\Support\Money;
use Modules\Support\Traits\HasCreatedBy;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasSortBy;

/**
 * Represents an immutable ledger entry for changes applied to a gift card balance.
 *
 * @property int $id
 * @property string $uuid
 * @property int $gift_card_id
 * @property int|null $order_id
 * @property GiftCardTransactionType $type
 * @property Money $amount
 * @property Money $balance_before
 * @property Money $balance_after
 * @property string $currency
 * @property float|null $exchange_rate
 * @property string|null $order_currency
 * @property Money|null $amount_in_order_currency
 * @property string|null $notes
 * @property Carbon|null $transaction_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read GiftCard|null $giftCard
 * @property-read Order|null $order
 */
class GiftCardTransaction extends Model
{
    use HasCreatedBy,
        HasFactory,
        HasBranch,
        HasFilters,
        HasSortBy;

    public static string $defaultDateColumn = 'transaction_at';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'gift_card_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'currency',
        'exchange_rate',
        'amount_in_order_currency',
        'order_currency',
        'order_id',
        'branch_id',
        'notes',
        'transaction_at',
        'group_by_date',
        'created_by',
    ];

    protected static function newFactory(): GiftCardTransactionFactory
    {
        return GiftCardTransactionFactory::new();
    }

    /**
     * Initialize generated fields before persisting a new transaction row.
     */
    protected static function booted(): void
    {
        static::creating(function (GiftCardTransaction $transaction) {
            if (!$transaction->uuid) {
                $transaction->uuid = (string)Str::uuid();
            }

            if (!$transaction->transaction_at) {
                $transaction->transaction_at = now();
            }
        });
    }

    /**
     * Get the supported filter keys for transaction listing endpoints.
     *
     * @return array<int, string>
     */
    public function allowedFilterKeys(): array
    {
        return [
            'search',
            'from',
            'to',
            self::BRANCH_COLUMN_NAME,
            'type',
            'gift_card_id',
            'created_by',
            'currency',
            'order_currency',
            'group_by_date'
        ];
    }

    /**
     * Apply a text search over the transaction, linked card, and linked order.
     */
    public function scopeSearch(Builder $query, string $value): void
    {
        $query->where(function (Builder $builder) use ($value) {
            $builder->like('uuid', $value)
                ->orLike('notes', $value)
                ->orWhereHas('giftCard', fn(Builder $giftCardQuery) => $giftCardQuery->like('code', $value))
                ->orWhereHas('order', fn(Builder $orderQuery) => $orderQuery->where('reference_no', $value));
        });
    }

    /**
     * Cast the transaction amount into a Money value object.
     *
     * @return Attribute<Money, static>
     */
    public function amount(): Attribute
    {
        return Attribute::get(fn($amount) => new Money($amount, $this->currency));
    }

    /**
     * Cast the previous balance into a Money value object.
     *
     * @return Attribute<Money, static>
     */
    public function balanceBefore(): Attribute
    {
        return Attribute::get(fn($amount) => new Money($amount, $this->currency));
    }

    /**
     * Cast the resulting balance into a Money value object.
     *
     * @return Attribute<Money, static>
     */
    public function balanceAfter(): Attribute
    {
        return Attribute::get(fn($amount) => new Money($amount, $this->currency));
    }

    /**
     * Cast the order-currency amount into a Money value object.
     *
     * @return Attribute<Money|null, static>
     */
    public function amountInOrderCurrency(): Attribute
    {
        return Attribute::get(function ($amount) {
            if (is_null($amount) || blank($this->order_currency)) {
                return null;
            }

            return new Money($amount, $this->order_currency);
        });
    }

    /**
     * Get the gift card that owns this ledger transaction.
     *
     * @return BelongsTo<GiftCard, $this>
     */
    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    /**
     * Get the related order when the transaction is tied to order activity.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withOutGlobalBranchPermission();
    }

    /**
     * Get the sortable attributes supported by the listing layer.
     *
     * @return array<int, string>
     */
    protected function getSortableAttributes(): array
    {
        return [
            'transaction_at',
            'type',
            'amount',
            'balance_before',
            'balance_after',
            'branch_id',
            'created_by',
        ];
    }

    /**
     * Cast model attributes to their domain-specific value objects and enums.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => GiftCardTransactionType::class,
            'transaction_at' => 'datetime',
            'exchange_rate' => 'float',
        ];
    }
}
