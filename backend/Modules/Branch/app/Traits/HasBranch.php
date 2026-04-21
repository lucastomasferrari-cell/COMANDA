<?php

namespace Modules\Branch\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Branch\Models\Branch;
use Modules\Branch\Scopes\BranchPermissionScope;
use Modules\Support\Scopes\ActiveScope;

/**
 * @property int|null $branch_id
 * @property Branch|null $branch
 * @property string $currency
 *
 * @method static Builder|static whereBranch(int $id)
 * @method static Builder|static withOutGlobalBranchPermission()
 */
trait HasBranch
{
    /**
     * The branch key.
     *
     * @var string
     */
    const BRANCH_COLUMN_NAME = 'branch_id';

    /**
     * Boot HasActiveStatus trait
     *
     * @return void
     */
    public static function bootHasBranch(): void
    {
        if (auth()->check() && auth()->user()->assignedToBranch()) {
            static::addGlobalScope("branch_permission", new BranchPermissionScope(auth()->user()));
            static::creating(function ($model) {
                $model->branch_id = auth()->user()->branch_id;
            });
        }
    }

    /**
     * Get a model branch
     *
     * @return BelongsTo
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, self::BRANCH_COLUMN_NAME)
            ->withTrashed()
            ->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Scope a query to only include records a specific branch.
     *
     * @param Builder $query
     * @param int $id
     * @return void
     */
    public function scopeWhereBranch(Builder $query, int $id): void
    {
        $query->where(static::BRANCH_COLUMN_NAME, $id);
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
}
