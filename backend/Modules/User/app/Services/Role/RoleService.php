<?php

namespace Modules\User\Services\Role;

use App\Forkiva;
use Arr;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\Support\GlobalStructureFilters;
use Modules\User\Facades\Permission;
use Modules\User\Models\Role;

class RoleService implements RoleServiceInterface
{
    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->with("createdBy")
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /** @inheritDoc */
    public function getModel(): Role
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return Role::class;
    }

    /** @inheritDoc */
    public function store(array $data): Role
    {
        $displayName = $data['display_name'];
        $role = $this->getModel()->create(
            [
                "display_name" => $displayName,
                "name" => Role::getSlugName($displayName)
            ]
        );

        if (isset($data['permissions'])) {
            $role->givePermissionTo($data['permissions']);
        }

        return $role;
    }

    /** @inheritDoc */
    public function update(int $id, array $data): Role
    {
        $role = $this->findOrFail($id);

        $role->update(Arr::only($data, ['display_name']));

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    /** @inheritDoc */
    public function findOrFail(int $id): Builder|array|EloquentCollection|Role
    {
        return $this->getModel()->findOrFail($id);
    }

    /** @inheritDoc */
    public function find(int $id): Builder|array|EloquentCollection|Role
    {
        return $this->getModel()->find($id);
    }

    /** @inheritDoc */
    public function show(int $id): Role
    {
        return $this->findOrFail($id);
    }

    /** @inheritDoc */
    public function label(): string
    {
        return __("user::roles.role");
    }

    /** @inheritDoc */
    public function destroy(int|string|array $ids): bool
    {
        return $this->getModel()
            ->notBuiltIn()
            ->whereIn('id', parseIds($ids))
            ->delete() ?: false;
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        return [
            [
                "key" => 'built_in',
                "label" => __('user::roles.filters.role_type'),
                "type" => 'select',
                "options" => [
                    [
                        "id" => 1,
                        "name" => __('user::roles.role_types.built_in'),
                    ],
                    [
                        "id" => 0,
                        "name" => __('user::roles.role_types.custom'),
                    ]
                ],
            ],
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function getFormMeta(): array
    {
        return [
            "permissions" => $this->getPermissions(),
        ];
    }

    /** @inheritDoc */
    public function getPermissions(): array
    {
        return Permission::formatPermissions();
    }
}
