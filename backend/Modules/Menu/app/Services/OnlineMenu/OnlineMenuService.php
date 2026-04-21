<?php

namespace Modules\Menu\Services\OnlineMenu;

use App\Forkiva;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\Branch\Models\Branch;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\OnlineMenu;
use Modules\Pos\Services\PosViewer\PosViewerServiceInterface;
use Modules\Support\GlobalStructureFilters;

class OnlineMenuService implements OnlineMenuServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("menu::online_menus.online_menu");
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->query()
            ->with(["branch:id,name", "menu:id,name"])
            ->withoutGlobalActive()
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /** @inheritDoc */
    public function getModel(): OnlineMenu
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return OnlineMenu::class;
    }

    /** @inheritDoc */
    public function show(int $id): OnlineMenu
    {
        return $this->findOrFail($id);
    }

    /** @inheritDoc */
    public function findOrFail(int $id): Builder|array|EloquentCollection|OnlineMenu
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function store(array $data): OnlineMenu
    {
        return $this->getModel()->query()->create($data);
    }

    /** @inheritDoc */
    public function update(int $id, array $data): OnlineMenu
    {
        $onlineMenu = $this->findOrFail($id);
        $onlineMenu->update($data);

        return $onlineMenu;
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
    public function getStructureFilters(?int $branchId): array
    {
        $branchFilter = GlobalStructureFilters::branch();
        return [
            ...(is_null($branchFilter) ? [] : [$branchFilter]),
            [
                "key" => 'menu_id',
                "label" => __('menu::online_menus.filters.menu'),
                "type" => 'select',
                "options" => !is_null($branchId) ? Menu::list($branchId, true) : [],
                "depends" => "branch_id"
            ],
            GlobalStructureFilters::active(),
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function getFormMeta(?int $branchId): array
    {
        if (is_null($branchId)) {
            return [
                "branches" => Branch::list(),
            ];
        } else {
            return [
                "menus" => Menu::list($branchId, true),
            ];
        }
    }

    /** @inheritDoc */
    public function getMenu(string $slug): array
    {
        $onlineMenu = $this->getModel()->findBySlug($slug, ["menu:id,name"]);

        abort_if(is_null($onlineMenu), 404);

        $menu = $onlineMenu->menu ?: Menu::getActiveMenu($onlineMenu->branch_id);

        $service = app(PosViewerServiceInterface::class);

        return [
            "categories" => $service->getCategories($menu->id),
            "products" => $service->getProducts($menu->id),
        ];
    }
}
