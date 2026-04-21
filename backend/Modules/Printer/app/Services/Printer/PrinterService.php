<?php

namespace Modules\Printer\Services\Printer;

use App\Forkiva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\Branch\Models\Branch;
use Modules\Printer\Enum\PrinterPaperSize;
use Modules\Printer\Models\Printer;
use Modules\Support\GlobalStructureFilters;

class PrinterService implements PrinterServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("printer::printers.printer");
    }

    /** @inheritDoc */
    public function show(int $id): Printer
    {
        return $this->findOrFail($id);
    }

    /** @inheritDoc */
    public function findOrFail(int $id): Builder|array|EloquentCollection|Printer
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function getModel(): Printer
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return Printer::class;
    }

    /** @inheritDoc */
    public function store(array $data): Printer
    {
        return $this->getModel()->query()->create($data);
    }

    /** @inheritDoc */
    public function update(int $id, array $data): Printer
    {
        $printer = $this->findOrFail($id);
        $printer->update($data);

        return $printer;
    }

    /** @inheritDoc */
    public function destroy(int|array|string $ids): bool
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->whereIn("id", parseIds($ids))
            ->delete() ?: false;
    }

    /** @inheritDoc */
    public function getStructureFilters(): array
    {
        $branchFilter = GlobalStructureFilters::branch();
        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            GlobalStructureFilters::active(),
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function getFormMeta(?int $branchId = null): array
    {
        return [
            "branches" => Branch::list(),
            "paper_sizes" => PrinterPaperSize::toArrayTrans(),
        ];
    }


    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->query()
            ->with(["branch:id,name"])
            ->withoutGlobalActive()
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }
}
