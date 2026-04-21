<?php

namespace Modules\User\Services\Customer;

use App\Forkiva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Modules\Support\Country;
use Modules\Support\GlobalStructureFilters;
use Modules\User\Enums\CustomerType;
use Modules\User\Enums\DefaultRole;
use Modules\User\Enums\GenderType;
use Modules\User\Models\User;

class CustomerService implements CustomerServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("user::users.user");
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
            ->role(DefaultRole::Customer)
            ->withoutGlobalActive()
            ->findOrFail($id);
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
    public function store(array $data): User
    {
        $data['customer_type'] = $data['customer_type'] ?? CustomerType::Regular->value;
        /** @var UploadedFile|null $profilePhoto */
        $profilePhoto = $data['profile_photo'] ?? null;

        /** @var User $customer */
        $customer = $this->getModel()
            ->query()
            ->create(Arr::except($data, ['profile_photo', 'remove_profile_photo']))
            ->assignRole(DefaultRole::Customer);

        if ($profilePhoto instanceof UploadedFile) {
            $customer->replaceProfilePhoto($profilePhoto);
            $customer->refresh();
        }

        return $customer;
    }

    /** @inheritDoc */
    public function update(int $id, array $data): User
    {
        $user = $this->findOrFail($id);

        $exceptAttributes = [];

        if (empty($data['password'])) {
            $exceptAttributes[] = "password";
        }

        /** @var UploadedFile|null $profilePhoto */
        $profilePhoto = $data['profile_photo'] ?? null;
        $removeProfilePhoto = (bool)($data['remove_profile_photo'] ?? false);

        $user->update(Arr::except($data, [...$exceptAttributes, 'profile_photo', 'remove_profile_photo']));

        if ($removeProfilePhoto) {
            $user->clearProfilePhoto();
        } else if ($profilePhoto instanceof UploadedFile) {
            $user->replaceProfilePhoto($profilePhoto);
        }

        return $user;
    }

    /** @inheritDoc */
    public function destroy(int|array|string $ids): bool
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->role(DefaultRole::Customer)
            ->whereIn("id", parseIds($ids))
            ->delete() ?: false;
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        return [
            [
                "key" => 'gender',
                "label" => __('user::customers.filters.gender'),
                "type" => 'select',
                "options" => GenderType::toArrayTrans(),
            ],
            GlobalStructureFilters::active(),
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function getFormMeta(): array
    {
        return [
            "genders" => GenderType::toArrayTrans(),
            "countries" => Country::toList(),
            "customer_types" => CustomerType::toArrayTrans(),
        ];
    }

    /** @inheritDoc */
    public function search(?string $query = null): Collection
    {
        if (is_null($query)) {
            return collect();
        }

        return $this->getModel()
            ->query()
            ->role(DefaultRole::Customer)
            ->search($query)
            ->limit(5)
            ->get();
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->role(DefaultRole::Customer)
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }
}
