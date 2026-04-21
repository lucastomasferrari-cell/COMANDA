<?php

namespace Modules\Product\Services\Product;

use App\Forkiva;
use Arr;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Modules\Category\Models\Category;
use Modules\Inventory\Models\Ingredient;
use Modules\Menu\Models\Menu;
use Modules\Option\Enums\OptionType;
use Modules\Option\Models\Option;
use Modules\Product\Enums\IngredientOperation;
use Modules\Product\Models\Product;
use Modules\Support\Enums\PriceType;
use Modules\Support\GlobalStructureFilters;
use Modules\Tax\Models\Tax;

class ProductService implements ProductServiceInterface
{
    /** @inheritDoc */
    public function label(): string
    {
        return __("product::products.product");
    }

    /** @inheritDoc */
    public function show(int $id, ?int $menuId = null): Product
    {
        return $this->getModel()
            ->query()
            ->with([
                "categories:id,menu_id",
                "taxes:id,branch_id",
                "files",
                "options" => fn($query) => $query->with(['values' => fn($query) => $query->with('ingredients')]),
                "ingredients"
            ])
            ->when(!is_null($menuId), fn(Builder $query) => $query->where('menu_id', $menuId))
            ->withoutGlobalActive()
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function findOrFail(int $id): Builder|array|EloquentCollection|Product
    {
        return $this->getModel()
            ->query()
            ->withoutGlobalActive()
            ->findOrFail($id);
    }

    /** @inheritDoc */
    public function getModel(): Product
    {
        return new ($this->model());
    }

    /** @inheritDoc */
    public function model(): string
    {
        return Product::class;
    }

    /** @inheritDoc */
    public function store(array $data): Product
    {
        $product = $this->getModel()
            ->query()
            ->create(
                Arr::except(
                    $data,
                    ['categories', 'options', 'ingredients', 'taxes']
                )
            );

        $product->categories()->attach(Arr::get($data, 'categories', []));
        $product->taxes()->attach(Arr::get($data, 'taxes', []));

        return $product;
    }

    /** @inheritDoc */
    public function get(array $filters = [], ?array $sorts = []): LengthAwarePaginator
    {
        return $this->getModel()
            ->query()
            ->with(['files'])
            ->withoutGlobalActive()
            ->filters($filters)
            ->sortBy($sorts)
            ->paginate(Forkiva::paginate())
            ->withQueryString();
    }

    /** @inheritDoc */
    public function update(int $id, array $data): Product
    {
        $product = $this->findOrFail($id);
        $product->update(
            Arr::except(
                $data,
                ['categories', 'options', 'ingredients', 'taxes']
            )
        );

        $product->categories()->sync(Arr::get($data, 'categories', []));
        $product->taxes()->sync(Arr::get($data, 'taxes', []));

        return $product;
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
        return [
            GlobalStructureFilters::active(),
            GlobalStructureFilters::from(),
            GlobalStructureFilters::to(),
        ];
    }

    /** @inheritDoc */
    public function getFormMeta(?Menu $menu = null): array
    {
        return [
            "menu_id" => $menu?->id,
            "categories" => !is_null($menu) ? Category::treeList($menu->id) : [],
            "taxes" => !is_null($menu) ? Tax::list($menu->branch_id, false) : [],
            "price_types" => PriceType::toArrayTrans(),
            "option_types" => OptionType::toArrayTrans(),
            "option_templates" => !is_null($menu) ? Option::listGlobal() : [],
            "currency" => !is_null($menu) ? $menu->branch->currency : setting("default_currency"),
            "ingredients" => !is_null($menu) ? Ingredient::list($menu->branch_id) : [],
            "ingredient_operations" => IngredientOperation::toArrayTrans()
        ];
    }
}
