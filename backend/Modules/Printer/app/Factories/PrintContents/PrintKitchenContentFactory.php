<?php

namespace Modules\Printer\Factories\PrintContents;

use Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Category\Models\Category;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderProduct;
use Modules\Printer\app\Factories\OrderResourceFactory;
use Modules\Printer\Contracts\PrintContentFactoryInterface;
use Modules\Printer\Models\Printer;
use Modules\User\Enums\DefaultRole;
use Modules\User\Models\User;

class PrintKitchenContentFactory implements PrintContentFactoryInterface
{
    /** @inheritDoc */
    public function relations(): array
    {
        return [
            "products.product.categories",
            "waiter:id,name",
            "table:id,name",
        ];
    }

    /** @inheritDoc */
    public function resource(Order $order): array
    {
        $kitchens = $this->getKitchens($order->branch_id);
        $data = [
            "order" => OrderResourceFactory::order($order),
            "waiter" => !is_null($order->waiter) ? OrderResourceFactory::waiter($order->waiter) : null,
            "table" => !is_null($order->table) ? OrderResourceFactory::table($order->table) : null,
            "kitchens" => []
        ];

        $parsedProducts = $order->products
            ->map(fn(OrderProduct $product) => OrderResourceFactory::product($product, true));

        /** @var User $kitchen */
        foreach ($kitchens as $kitchen) {

            $kitchenSlugs = $kitchen->category_slugs;
            $kitchenInfo = [
                "id" => $kitchen->id,
                "name" => $kitchen->name,
                "printer_id" => $kitchen->printer_id,
            ];

            if (empty($kitchenSlugs)) {
                $data["kitchens"][$kitchen->id] = [
                    'kitchen' => $kitchenInfo,
                    "products" => $parsedProducts
                ];
                continue;
            }

            $allowedSlugs = $this->resolveCategoryTreeSlugs($kitchenSlugs);

            $filteredProducts = $order->products
                ->filter(function (OrderProduct $orderProduct) use ($allowedSlugs) {
                    return $orderProduct->product->categories->isEmpty()
                        || $orderProduct->product->categories
                            ->pluck('slug')
                            ->intersect($allowedSlugs)
                            ->isNotEmpty();
                });

            if ($filteredProducts->isNotEmpty()) {
                $data["kitchens"][$kitchen->id] = [
                    'kitchen' => $kitchenInfo,
                    "products" => $filteredProducts
                        ->map(fn(OrderProduct $filteredProduct) => OrderResourceFactory::product($filteredProduct, true))
                ];
            }
        }

        return $data;
    }

    /**
     * Get kitchens for print
     *
     * @param int $branchId
     * @return Collection
     */
    public function getKitchens(int $branchId): Collection
    {
        return Cache::tags("users")
            ->rememberForever(
                makeCacheKey(
                    [
                        'users',
                        "branch-$branchId",
                        "role-" . DefaultRole::Kitchen->value,
                        "printers"
                    ],
                    false
                ),
                fn() => User::query()
                    ->role(DefaultRole::Kitchen->value)
                    ->where('branch_id', $branchId)
                    ->withOutGlobalBranchPermission()
                    ->whereHas('printer', fn($query) => $query->whereNotNull('options->qintrix_id'))
                    ->get()
            );
    }

    /**
     * Resolve category tree slugs
     *
     * @param array $slugs
     * @return array
     */
    public function resolveCategoryTreeSlugs(array $slugs): array
    {
        if (empty($slugs)) {
            return [];
        }

        $categories = Category::query()
            ->whereIn('slug', $slugs)
            ->get();

        if ($categories->isEmpty()) {
            return [];
        }

        return Category::query()
            ->where(function ($q) use ($categories) {
                foreach ($categories as $cat) {
                    $q->orWhere(function ($sub) use ($cat) {
                        $sub->where('_lft', '>=', $cat->_lft)
                            ->where('_rgt', '<=', $cat->_rgt);
                    });
                }
            })
            ->pluck('slug')
            ->unique()
            ->values()
            ->all();
    }

    /** @inheritDoc */
    public function printers(int|array $specificIds): array|Printer|null
    {
        return User::query()
            ->whereIn('id', Arr::wrap($specificIds))
            ->whereHas('printer', fn($query) => $query->whereNotNull('options->qintrix_id'))
            ->with([
                'printer' => fn($query) => $query->whereNotNull('options->qintrix_id'),
            ])
            ->withoutGlobalBranchPermission()
            ->get()
            ->mapWithKeys(function (User $user) {
                return [
                    $user->id => $user->printer
                ];
            })
            ->all();
    }
}
