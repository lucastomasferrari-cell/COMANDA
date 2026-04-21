<?php

namespace Modules\GiftCard\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\ActivityLog\Traits\HasActivityLog;
use Modules\Branch\Traits\HasBranch;
use Modules\GiftCard\Database\Factories\GiftCardFactory;
use Modules\GiftCard\Enums\GiftCardScope;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\GiftCard\Services\GiftCardCode\GiftCardCodeServiceInterface;
use Modules\Support\Eloquent\Model;
use Modules\Support\Money;
use Modules\Support\Traits\HasCreatedBy;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasSortBy;
use Modules\Translation\Traits\Translatable;
use Modules\User\Models\User;

/**
 * Represents a stored-value gift card that can be sold, redeemed, recharged,
 * refunded into, and expired within branch or global scope.
 *
 * @property int $id
 * @property string $uuid
 * @property string $code
 * @property int|null $customer_id
 * @property int|null $gift_card_batch_id
 * @property GiftCardScope $scope
 * @property GiftCardStatus $status
 * @property Money $initial_balance
 * @property Money $current_balance
 * @property string $currency
 * @property Carbon|null $expiry_date
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $customer
 * @property-read GiftCardBatch|null $batch
 * @property-read Collection<int, GiftCardTransaction> $transactions
 */
class GiftCard extends Model
{
    use HasActivityLog,
        HasCreatedBy,
        HasBranch,
        HasFactory,
        HasFilters,
        Translatable,
        HasSortBy,
        SoftDeletes;


    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected array $translatable = ['branch_name'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'code',
        'customer_id',
        'gift_card_batch_id',
        'scope',
        'status',
        'initial_balance',
        'current_balance',
        'currency',
        'expiry_date',
        'notes',
        self::BRANCH_COLUMN_NAME
    ];

    /**
     * Find a gift card by its case-sensitive code.
     */
    public static function findByCode(string $code): ?self
    {
        $normalizedCode = static::codeService()->normalize($code);

        return static::query()
            ->whereRaw("REPLACE(REPLACE(UPPER(`code`), '-', ''), ' ', '') = ?", [$normalizedCode])
            ->first();
    }

    /**
     * Resolve the shared gift card code service from the container.
     */
    protected static function codeService(): GiftCardCodeServiceInterface
    {
        return app(GiftCardCodeServiceInterface::class);
    }

    /**
     * Generate a unique gift card code in a globally recognizable format.
     */
    public static function generateUniqueCode(?string $prefix = null): string
    {
        return static::codeService()->generate($prefix);
    }

    protected static function newFactory(): GiftCardFactory
    {
        return GiftCardFactory::new();
    }

    /**
     * Initialize generated attributes before persisting a new gift card.
     */
    protected static function booted(): void
    {
        static::creating(function (GiftCard $giftCard) {
            if (!$giftCard->uuid) {
                $giftCard->uuid = (string)Str::uuid();
            }

            if (!$giftCard->code) {
                $giftCard->code = static::codeService()->generate();
            }

            if (is_null($giftCard->current_balance)) {
                $giftCard->current_balance = $giftCard->initial_balance;
            }
        });
    }

    /**
     * Get the supported filter keys for listing endpoints.
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
            'customer_id',
            'gift_cards.currency',
            'status',
            'scope',
            'gift_card_batch_id',
            'expiry_from',
            'expiry_to',
            'min_balance',
            'max_balance',
            'group_by_date'
        ];
    }

    /**
     * Apply a text search over code, UUID, notes, and linked customer data.
     */
    public function scopeSearch(Builder $query, string $value): void
    {
        $query->where(function (Builder $builder) use ($value) {
            $builder->like('code', $value)
                ->orLike('uuid', $value)
                ->orWhereHas('customer', fn(Builder $customerQuery) => $customerQuery->search($value));
        });
    }

    /**
     * Filter cards by minimum expiry date.
     */
    public function scopeExpiryFrom(Builder $query, string $value): void
    {
        $query->whereDate('expiry_date', '>=', $value);
    }

    /**
     * Filter cards by maximum expiry date.
     */
    public function scopeExpiryTo(Builder $query, string $value): void
    {
        $query->whereDate('expiry_date', '<=', $value);
    }

    /**
     * Filter cards by minimum remaining balance.
     */
    public function scopeMinBalance(Builder $query, float|int|string $value): void
    {
        $query->where('current_balance', '>=', $value);
    }

    /**
     * Filter cards by maximum remaining balance.
     */
    public function scopeMaxBalance(Builder $query, float|int|string $value): void
    {
        $query->where('current_balance', '<=', $value);
    }

    /**
     * Cast the persisted initial balance into a Money value object.
     *
     * @return Attribute<Money, static>
     */
    public function initialBalance(): Attribute
    {
        return Attribute::get(fn($amount) => new Money($amount, $this->currency));
    }

    /**
     * Cast the persisted current balance into a Money value object.
     *
     * @return Attribute<Money, static>
     */
    public function currentBalance(): Attribute
    {
        return Attribute::get(fn($amount) => new Money($amount, $this->currency));
    }

    /**
     * Get the linked customer, if the card is assigned to one.
     *
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id')
            ->withOutGlobalBranchPermission()
            ->withoutGlobalActive()
            ->withTrashed();
    }

    /**
     * Get the batch that generated the gift card, if any.
     *
     * @return BelongsTo<GiftCardBatch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(GiftCardBatch::class, 'gift_card_batch_id');
    }

    /**
     * Get the ledger transactions recorded against the gift card.
     *
     * @return HasMany<GiftCardTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(GiftCardTransaction::class);
    }

    /**
     * Determine whether the card can currently be used in operational flows.
     */
    public function isUsable(): bool
    {
        return !$this->isExpired()
            && !$this->isDisabled()
            && in_array($this->status, [GiftCardStatus::Active, GiftCardStatus::Used], true);
    }

    /**
     * Determine whether the card has passed its expiry date.
     */
    public function isExpired(): bool
    {
        return !is_null($this->expiry_date) && $this->expiry_date->isPast();
    }

    /**
     * Determine whether the card has been manually disabled.
     */
    public function isDisabled(): bool
    {
        return $this->status === GiftCardStatus::Disabled;
    }

    /**
     * Get the sortable attributes supported by the model listing layer.
     *
     * @return array<int, string>
     */
    protected function getSortableAttributes(): array
    {
        return [
            'code',
            'initial_balance',
            'current_balance',
            'currency',
            'status',
            'scope',
            self::BRANCH_COLUMN_NAME,
            'customer_id',
            'expiry_date',
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
            'scope' => GiftCardScope::class,
            'status' => GiftCardStatus::class,
            'expiry_date' => 'datetime',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }
}
