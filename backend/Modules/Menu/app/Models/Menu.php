<?php

namespace Modules\Menu\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\ActivityLog\Traits\HasActivityLog;
use Modules\Branch\Traits\HasBranch;
use Modules\Menu\Database\Factories\MenuFactory;
use Modules\Product\Services\SkuAllocator;
use Modules\Support\Eloquent\Model;
use Modules\Support\Traits\HasActiveStatus;
use Modules\Support\Traits\HasCreatedBy;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasSortBy;
use Modules\Support\Traits\HasTagsCache;
use Modules\Support\Traits\HasUuid;
use Modules\Translation\Traits\Translatable;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Menu extends Model
{
    use HasActiveStatus,
        HasActivityLog,
        HasFactory,
        HasCreatedBy,
        HasBranch,
        HasTagsCache,
        HasSortBy,
        HasFilters,
        HasUuid,
        Translatable,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'sku',
        'sku_locked',
        self::ACTIVE_COLUMN_NAME,
        self::BRANCH_COLUMN_NAME
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected array $translatable = ['name', 'description'];

    /**
     * Get a list of all menus.
     *
     * @param bool $withoutGlobalActive
     * @param int|null $branchId
     * @return Collection
     */
    public static function list(?int $branchId = null, bool $withoutGlobalActive = false): Collection
    {
        return Cache::tags("menus")
            ->rememberForever(
                makeCacheKey([
                    'menus',
                    is_null($branchId) ? 'all' : "branch-{$branchId}",
                    $withoutGlobalActive ? 'all' : 'active',
                    'list'
                ]),
                fn() => static::select('id', 'name')
                    ->when(!is_null($branchId), fn($query) => $query->whereBranch($branchId))
                    ->when($withoutGlobalActive, fn($query) => $query->withoutGlobalActive())
                    ->orderBy("is_active", "desc")
                    ->get()
                    ->map(fn(Menu $menu) => [
                        'id' => $menu->id,
                        'name' => $menu->name,
                    ])
            );
    }

    /**
     * Get active menu
     *
     * @param int $branchId
     * @param bool $withBranch
     * @return Menu|null
     */
    public static function getActiveMenu(int $branchId, bool $withBranch = false): ?Menu
    {
        return static::where('branch_id', $branchId)
            ->when($withBranch, fn(Builder $query) => $query->with(["branch"]))
            ->where('is_active', true)
            ->withOutGlobalBranchPermission()
            ->latest()
            ->first();
    }

    protected static function newFactory(): MenuFactory
    {
        return MenuFactory::new();
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        // Auto-asigna SKU secuencial si el caller no lo proveyó. Ver
        // Product::booted() para detalle del patrón.
        static::creating(function (Menu $menu) {
            if (empty($menu->sku)) {
                $menu->sku = SkuAllocator::next('menus');
            }
        });

        static::saved(fn(Menu $menu) => $menu->syncActiveMenu());
    }

    /**
     * Sync active agent
     *
     * @return void
     */
    public function syncActiveMenu(): void
    {
        $this->withoutEvents(function () {
            $branchId = $this->branch_id;
            if ($this->is_active) {
                self::withoutGlobalActive()
                    ->withOutGlobalBranchPermission()
                    ->where('id', '<>', $this->id)
                    ->where('branch_id', $branchId)
                    ->update(['is_active' => false]);
            } elseif (!static::hasActiveMenu($this->id, $branchId)) {
                self::withoutGlobalActive()
                    ->withOutGlobalBranchPermission()
                    ->where('id', $this->id)
                    ->where('branch_id', $branchId)
                    ->update(['is_active' => true]);
            }
        });
    }

    /**
     * Determine id has active menu
     *
     * @param int|null $exceptionId
     * @param int|null $branchId
     * @return bool
     */
    public static function hasActiveMenu(?int $exceptionId = null, ?int $branchId = null): bool
    {
        return static::withoutGlobalActive()
            ->withOutGlobalBranchPermission()
            ->where(function ($query) use ($exceptionId) {
                $query->where('id', '<>', $exceptionId);
            })
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->exists();
    }

    /** @inheritDoc */
    public function allowedFilterKeys(): array
    {
        return [
            "search",
            "from",
            "to",
            self::BRANCH_COLUMN_NAME,
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

    /** @inheritDoc */
    protected function getSortableAttributes(): array
    {
        return [
            "name",
            self::BRANCH_COLUMN_NAME,
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
            'sku_locked' => "boolean",
            self::ACTIVE_COLUMN_NAME => "boolean",
        ];
    }

}
