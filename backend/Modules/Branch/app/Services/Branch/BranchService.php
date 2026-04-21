<?php

namespace Modules\Branch\Services\Branch;

use App\Forkiva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\Branch\Models\Branch;
use Modules\Currency\Currency;
use Modules\Order\Enums\OrderType;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Support\Country;
use Modules\Support\GlobalStructureFilters;
use Modules\Support\TimeZone;

class BranchService implements BranchServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("branch::branches.branch");
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->withoutGlobalActive()
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /** @inheritDoc */
    public function getModel(): Branch
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return Branch::class;
    }

    /** @inheritDoc */
    public function show(int $id): Branch
    {
        return $this->findOrFail($id);
    }

    /** @inheritDoc */
    public function findOrFail(int $id): Builder|array|EloquentCollection|Branch
    {
        return $this->getModel()
            ->withoutGlobalActive()
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function store(array $data): Branch
    {
        return $this->getModel()->create($data);
    }

    /** @inheritDoc */
    public function update(int $id, array $data): Branch
    {

        $branch = $this->findOrFail($id);
        $branch->update($data);

        return $branch;
    }

    /** @inheritDoc */
    public function destroy(int|array|string $ids): bool
    {
        return $this->getModel()
            ->withoutGlobalActive()
            ->notMain()
            ->whereIn("id", parseIds($ids))
            ->delete() ?: false;
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        return [
            GlobalStructureFilters::active(),
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function getFormMeta(): array
    {
        return [
            "timezones" => TimeZone::toList(),
            "countries" => Country::supportedList(),
            "currencies" => Currency::supportedList(),
            "order_types" => OrderType::toArrayTrans(),
            "payment_methods" => PaymentMethod::toArrayTrans(),
        ];
    }
}

