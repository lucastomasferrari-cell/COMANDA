<?php

namespace Modules\Menu\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Menu\Models\Menu;
use Modules\Menu\Scopes\MenuBranchPermissionScope;

/**
 * @property int $menu_id
 * @property-read  Menu $menu
 *
 * @method static Builder|static whereMenu(int $id)
 * @method static Builder|static withOutGlobalBranchPermission()
 */
trait HasMenu
{
    /**
     * The menu key.
     *
     * @var string
     */
    const MENU_COLUMN_NAME = 'menu_id';

    /**
     * Boot HasActiveStatus trait
     *
     * @return void
     */
    public static function bootHasMenu(): void
    {
        if (auth()->check() && auth()->user()->assignedToBranch()) {
            static::addGlobalScope("branch_permission", new MenuBranchPermissionScope(auth()->user()));
        }
    }

    /**
     * Get a model menu
     *
     * @return BelongsTo
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, self::MENU_COLUMN_NAME)
            ->withoutGlobalActive()
            ->withTrashed();
    }

    /**
     * Scope a query without global branch permission scope.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeWithOutGlobalBranchPermission(Builder $query): void
    {
        $query->withOutGlobalScope("branch_permission");
    }

    /**
     * Scope a query to only include records a specific menu.
     *
     * @param Builder $query
     * @param int $id
     * @return void
     */
    public function scopeWhereMenu(Builder $query, int $id): void
    {
        $query->where(static::MENU_COLUMN_NAME, $id);
    }
}
