<?php

namespace Modules\Loyalty\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\ActivityLog\Traits\HasActivityLog;
use Modules\Loyalty\Database\Factories\LoyaltyProgramFactory;
use Modules\Support\Eloquent\Model;
use Modules\Support\Money;
use Modules\Support\Traits\HasActiveStatus;
use Modules\Support\Traits\HasCreatedBy;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasSortBy;
use Modules\Support\Traits\HasTagsCache;
use Modules\Translation\Traits\Translatable;

/**
 * @property int $id
 * @property string $name
 * @property Money $earning_rate
 * @property Money $redemption_rate
 * @property int $min_redeem_points
 * @property int|null $points_expire_after
 * @property Collection<LoyaltyTier> $tiers
 * @property Collection<LoyaltyCustomer> $customers
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class LoyaltyProgram extends Model
{
    use HasActiveStatus,
        HasActivityLog,
        HasCreatedBy,
        HasTagsCache,
        HasSortBy,
        HasFilters,
        HasFactory,
        SoftDeletes,
        Translatable;

    /**
     * Default date column
     *
     * @var string
     */
    public static string $defaultDateColumn = 'created_at';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "name",
        "earning_rate",
        "redemption_rate",
        "min_redeem_points",
        "points_expire_after",
        self::ACTIVE_COLUMN_NAME,
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected array $translatable = ['name'];

    /**
     * Get a list of all loyalty programs.
     *
     * @return Collection
     */
    public static function list(): Collection
    {
        return Cache::tags("loyalty_programs")
            ->rememberForever(makeCacheKey(['loyalty_programs', 'list']),
                fn() => static::select('id', 'name')
                    ->withoutGlobalActive()
                    ->latest()
                    ->get()
                    ->map(fn(LoyaltyProgram $loyaltyProgram) => [
                        'id' => $loyaltyProgram->id,
                        'name' => $loyaltyProgram->name,
                    ])
            );
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saved(function (LoyaltyProgram $program) {
            if ($program->is_active) {
                static::withoutGlobalActive()
                    ->where('id', '!=', $program->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    protected static function newFactory(): LoyaltyProgramFactory
    {
        return LoyaltyProgramFactory::new();
    }

    /** @inheritDoc */
    public function allowedFilterKeys(): array
    {
        return [
            "search",
            "from",
            "to",
            "group_by_date",
            self::ACTIVE_COLUMN_NAME,
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
        $query->whereLikeTranslation('name', $value);
    }

    /**
     * Get earning rate
     *
     * @return Attribute
     */
    public function earningRate(): Attribute
    {
        return Attribute::get(fn($earningRate) => Money::inDefaultCurrency($earningRate));
    }

    /**
     * Get redemption rate
     *
     * @return Attribute
     */
    public function redemptionRate(): Attribute
    {
        return Attribute::get(fn($redemptionRate) => Money::inDefaultCurrency($redemptionRate));
    }

    /**
     * Get tiers
     *
     * @return HasMany
     */
    public function tiers(): HasMany
    {
        return $this->hasMany(LoyaltyTier::class)
            ->withTrashed()
            ->withoutGlobalActive();
    }

    /**
     * Get customer subscript in this loyalty program
     *
     * @return HasMany
     */
    public function customers(): HasMany
    {
        return $this->hasMany(LoyaltyCustomer::class);
    }

    /** @inheritDoc */
    protected function getSortableAttributes(): array
    {
        return [
            "name",
            "earning_rate",
            "redemption_rate",
            "min_redeem_points",
            "points_expire_after",
            self::ACTIVE_COLUMN_NAME,
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            self::ACTIVE_COLUMN_NAME => "boolean",
            "min_redeem_points" => "int",
            "points_expire_after" => "int",
            "end_date" => "datetime",
            "start_date" => "datetime",
        ];
    }
}
