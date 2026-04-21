<?php

namespace Modules\User\Services\User;

use App\Forkiva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Modules\Branch\Models\Branch;
use Modules\Category\Models\Category;
use Modules\Printer\Models\Printer;
use Modules\Support\GlobalStructureFilters;
use Modules\User\Enums\DefaultRole;
use Modules\User\Enums\GenderType;
use Modules\User\Models\Role;
use Modules\User\Models\User;
use Modules\User\Services\Role\RoleServiceInterface;

class UserService implements UserServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("user::users.user");
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->role(roles: DefaultRole::Customer, without: true)
            ->with("branch:id,name")
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /** @inheritDoc */
    public function getModel(): User
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return User::class;
    }

    /** @inheritDoc */
    public function show(int $id): User
    {
        return $this->findOrFail($id);
    }

    /** @inheritDoc */
    public function findOrFail(int $id): Builder|array|EloquentCollection|User
    {
        return $this->getModel()
            ->query()
            ->role(roles: DefaultRole::Customer, without: true)
            ->withoutGlobalActive()
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function store(array $data): User
    {
        $role = app(RoleServiceInterface::class)->find($data['role']);

        if ($role->name != DefaultRole::Kitchen->value) {
            $data['printer_id'] = null;
            $data['category_slugs'] = null;
        }

        $user = $this->getModel()->query()->create(Arr::except($data, ["role"]));

        $user->syncRoles([$data['role']]);

        return $user;
    }

    /** @inheritDoc */
    public function update(int $id, array $data): User
    {
        $user = $this->findOrFail($id);
        $role = app(RoleServiceInterface::class)->find($data['role']);

        if ($role->name != DefaultRole::Kitchen->value) {
            $data['printer_id'] = null;
            $data['category_slugs'] = null;
        }

        if ($user->isMainUser()) {
            $data["is_active"] = true;
        }

        $exceptAttributes = ["role"];

        if (empty($data['password'])) {
            $exceptAttributes[] = "password";
        }

        $user->update(Arr::except($data, $exceptAttributes));

        if (!$user->isMainUser()) {
            $user->syncRoles([$data['role']]);
        }

        return $user;
    }

    /** @inheritDoc */
    public function destroy(int|array|string $ids): bool
    {
        return $this->getModel()
            ->query()
            ->role(roles: DefaultRole::Customer, without: true)
            ->withoutGlobalActive()
            ->whereNot("id", 1)
            ->whereIn("id", parseIds($ids))
            ->delete() ?: false;
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        $branchFilter = GlobalStructureFilters::branch();
        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            [
                "key" => 'role',
                "label" => __('user::users.filters.role'),
                "type" => 'select',
                "options" => Role::list(auth()->user()->assignedToBranch()),
            ],
            [
                "key" => 'gender',
                "label" => __('user::users.filters.gender'),
                "type" => 'select',
                "options" => GenderType::toArrayTrans(),
            ],
            GlobalStructureFilters::active(),
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function getFormMeta(?int $branchId = null): array
    {
        if (is_null($branchId)) {
            return [
                "roles" => Role::list(auth()->user()->assignedToBranch()),
                "branches" => Branch::list(),
                "genders" => GenderType::toArrayTrans(),
                "categories" => Category::listWithSlug(),
                "branch_available_roles" => DefaultRole::getBranchAvailableRoles(),
            ];
        } else {
            return [
                "printers" => Printer::list($branchId),
            ];
        }
    }
}
