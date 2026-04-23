<?php

namespace Modules\Menu\Services\Menu;

use App\Forkiva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\Branch\Models\Branch;
use Modules\Menu\Models\Menu;
use Modules\Product\Services\SkuAllocator;
use Modules\Support\GlobalStructureFilters;

class MenuService implements MenuServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("menu::menus.menu");
    }

    /** @inheritDoc */
    public function show(int $id): Menu
    {
        return $this->findOrFail($id);
    }

    /** @inheritDoc */
    public function findOrFail(int $id): Builder|array|EloquentCollection|Menu
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function getModel(): Menu
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return Menu::class;
    }

    /** @inheritDoc */
    public function store(array $data): Menu
    {
        return $this->getModel()->query()->create($data);
    }

    /** @inheritDoc */
    public function update(int $id, array $data): Menu
    {
        $menu = $this->findOrFail($id);
        SkuAllocator::assertNotLocked($menu, $data, 'menu::messages.sku_locked');

        $menu->update($data);

        return $menu;
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
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function getFormMeta(): array
    {
        return [
            "branches" => Branch::list(),
        ];
    }

    /** @inheritDoc */
    public function getCurrentMenu(?int $menuId = null, bool $withBranch = false): ?Menu
    {
        $user = auth()->user();

        if (!is_null($menuId)) {
            return Menu::query()
                ->withoutGlobalActive()
                ->when($withBranch, fn(Builder $query) => $query->with(["branch"]))
                ->where('id', $menuId)
                ->first();
        }

        if ($user->assignedToBranch()) {
            return Menu::getActiveMenu($user->branch_id, $withBranch);
        } else {
            $mainBranch = Branch::query()->withoutGlobalActive()->main()->first();
            if (!is_null($mainBranch)) {
                return Menu::getActiveMenu($mainBranch->id, $withBranch);
            }
        }

        return null;
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
