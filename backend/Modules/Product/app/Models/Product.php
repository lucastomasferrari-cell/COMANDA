<?php

namespace Modules\Product\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\ActivityLog\Traits\HasActivityLog;
use Modules\Branch\Models\Branch;
use Modules\Branch\Traits\HasBranchCurrency;
use Modules\Category\Models\Category;
use Modules\Media\Models\Media;
use Modules\Media\Traits\HasMedia;
use Modules\Menu\Models\Menu;
use Modules\Menu\Traits\HasMenu;
use Modules\Option\Models\Option;
use Modules\Product\Database\Factories\ProductFactory;
use Modules\Product\Traits\HasSpecialPrice;
use Modules\Product\Traits\IsNew;
use Modules\Support\Eloquent\Model;
use Modules\Support\Enums\PriceType;
use Modules\Support\Money;
use Modules\Support\Traits\HasActiveStatus;
use Modules\Support\Traits\HasCreatedBy;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasSortBy;
use Modules\Support\Traits\HasTagsCache;
use Modules\Tax\Models\Tax;
use Modules\Translation\Traits\Translatable;
use Znck\Eloquent\Traits\BelongsToThrough;

/**
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property string $description
 * @property Collection<Tax> $taxes
 * @property-read  Tax $tax
 * @property Money $price
 * @property Money $selling_price
 * @property-read Media|null $thumbnail
 * @property-read string $currency
 * @property-read Branch $branch
 * @property-read Collection<Category> $categories
 * @property-read Collection<Ingredientable> $ingredients
 * @property-read Collection<Option> $options
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Product extends Model
{
    use HasActiveStatus,
        HasActivityLog,
        HasFactory,
        HasCreatedBy,
        HasTagsCache,
        HasSortBy,
        HasFilters,
        HasMenu,
        IsNew,
        HasSpecialPrice,
        HasMedia,
        Translatable,
        HasBranchCurrency,
        BelongsToThrough,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'special_price',
        'special_price_type',
        'special_price_start',
        'special_price_end',
        'new_from',
        'new_to',
        'sku',
        self::ACTIVE_COLUMN_NAME,
        self::MENU_COLUMN_NAME
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ["branch:branches.id,branches.currency"];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected array $translatable = ['name', 'description'];

    /**
     * Get a list of all products group by sku.
     *
     * @return Collection
     */
    public static function listBySku(): Collection
    {
        return Cache::tags("products")
            ->rememberForever(
                makeCacheKey(['products', 'sku', 'list']),
                fn() => static::select('id', 'name', 'sku')
                    ->without(["branch"])
                    ->whereNotNull('sku')
                    ->groupBy('sku')
                    ->get()
                    ->map(fn(Product $product) => [
                        'id' => $product->sku,
                        'name' => $product->name
                    ])
            );
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    /**
     * Get the categories associated with the product.
     *
     * @return BelongsToMany<Category>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * Get the taxes configuration assigned to the product.
     *
     * @return BelongsToMany<Tax>
     */
    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'product_taxes')
            ->withOutGlobalBranchPermission()
            ->withoutGlobalActive();
    }

    /** @inheritDoc */
    public function allowedFilterKeys(): array
    {
        return [
            "search",
            "from",
            "to",
            self::ACTIVE_COLUMN_NAME,
            self::MENU_COLUMN_NAME,
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
        $query->where(fn(Builder $query) => $query->whereLikeTranslation('name', $value)->orLike('sku', $value));
    }

    /**
     * Get selling price
     *
     * @return Attribute<Money>
     */
    public function sellingPrice(): Attribute
    {
        return Attribute::get(fn() => ($this->hasSpecialPrice() ? $this->getSpecialPrice() : $this->price));
    }

    /**
     * Get product thumbnail
     *
     * @return Attribute<Media|null>
     */
    public function thumbnail(): Attribute
    {
        return Attribute::get(fn() => $this->relationLoaded('files') ? $this->files->where('pivot.zone', 'thumbnail')->first() : null);
    }

    /**
     * Get branch
     *
     * @return \Znck\Eloquent\Relations\BelongsToThrough
     */
    public function branch(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(Branch::class, Menu::class);
    }

    /**
     * Get currency
     *
     * @return Attribute
     */
    public function currency(): Attribute
    {
        return Attribute::get(
            fn() => $this->relationLoaded("branch")
                ? ($this->branch?->currency ?: setting('default_currency'))
                : setting('default_currency')
        );
    }

    /**
     * Get price
     *
     * @return Attribute
     */
    public function price(): Attribute
    {
        return Attribute::get(fn($price) => new Money($price, $this->currency));
    }

    /**
     * Get options
     *
     * @return BelongsToMany
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'product_options')
            ->orderBy('order')
            ->with('values')
            ->withTrashed();
    }

    /**
     * Has any option
     *
     * @return bool
     */
    public function hasAnyOption(): bool
    {
        return $this->getAttribute('options')->isNotEmpty();
    }

    /**
     * Get ingredients
     *
     * @return MorphMany
     */
    public function ingredients(): MorphMany
    {
        return $this->morphMany(Ingredientable::class, 'ingredientable')
            ->orderBy('order');
    }

    /** @inheritDoc */
    protected function getSortableAttributes(): array
    {
        return [
            "name",
            "price",
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
            'special_price_type' => PriceType::class,
            'new_from' => "datetime",
            'new_to' => "datetime",
            'special_price_start' => "datetime",
            'special_price_end' => "datetime",
            self::ACTIVE_COLUMN_NAME => "boolean",
        ];
    }
}
