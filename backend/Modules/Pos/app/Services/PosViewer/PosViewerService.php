<?php

namespace Modules\Pos\Services\PosViewer;

use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Modules\Branch\Models\Branch;
use Modules\Cart\Facades\Cart;
use Modules\Category\Models\Category;
use Modules\Discount\Models\Discount;
use Modules\Menu\Models\Menu;
use Modules\Order\Enums\OrderType;
use Modules\Pos\Enums\PosCashDirection;
use Modules\Pos\Enums\PosCashReason;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Transformers\Api\V1\Pos\PosCategoryResource;
use Modules\Pos\Transformers\Api\V1\Pos\PosProductResource;
use Modules\Printer\Models\PrintAgent;
use Modules\Product\Models\Product;
use Modules\User\Enums\DefaultRole;
use Modules\User\Models\User;

class PosViewerService implements PosViewerServiceInterface
{
    /** @inheritDoc
     * @throws InvalidConditionException
     */
    public function getConfiguration(?int $branchId = null): array
    {
        Cart::clear();
        $user = auth()->user();
        $branch = null;
        $data = [
            "branches" => [],
            "menus" => [],
            "registers" => [],
            "order_types" => [],
            "discounts" => [],
            "branch_id" => $branchId,
            "menu_id" => null,
            "register_id" => null,
            "session_id" => null,
            "currency" => setting("default_currency"),
            "directions" => [],
            "reasons" => []
        ];

        if (is_null($data['branch_id'])) {
            if ($user->assignedToBranch()) {
                $data['branches'][] = [
                    "id" => $user->branch->id,
                    "name" => $user->branch->name,
                    "currency" => $user->branch->currency,
                ];
                $branch = $user->branch;
                $data["branch_id"] = $user->branch->id;
            } else {
                $data['branches'] = Branch::list();
            }
            $data["customers"] = User::list(defaultRole: DefaultRole::Customer);

            if ($user->can('admin.pos_cash_movements.create')) {
                $directions = PosCashDirection::toArrayTrans([PosCashDirection::Adjust->value]);
                $reasons = [];

                foreach ($directions as $direction) {
                    $reasons[$direction['id']] = array_map(
                        fn(PosCashReason $reason) => $reason->toTrans(),
                        PosCashReason::getForManageCashMovement(PosCashDirection::from($direction['id']))
                    );
                }
                $data["directions"] = $directions;
                $data["reasons"] = $reasons;
            }
        }

        if (count($data['branches']) > 0 || !is_null($data['branch_id'])) {
            if (is_null($data['branch_id'])) {
                $data['branch_id'] = $data['branches'][0]['id'];
            }

            $branch = $branch ?? Branch::select(
                'id',
                'name',
                'order_types',
                'currency')
                ->findOrFail($data['branch_id']);

            $data['currency'] = $branch->currency;
            Cart::addBranch($branch);

            $orderTypes = $branch->order_types ?: [];
            $isWaiter = $user->hasRole(DefaultRole::Waiter->value);
            if ($isWaiter && in_array(OrderType::DineIn->value, $orderTypes)) {
                $orderTypes = [OrderType::DineIn->value];
            }

            if (!$isWaiter && count($orderTypes) > 0) {
                Cart::addOrderType(OrderType::from($orderTypes[0]));
            }

            if (!$isWaiter) {
                $data["waiters"] = User::list($data['branch_id'], DefaultRole::Waiter);
            }

            $data["discounts"] = Discount::list($data['branch_id']);

            $data['order_types'] = array_values(array_filter(
                OrderType::toArrayTrans(),
                fn($orderType) => in_array($orderType['id'], $orderTypes)
            ));
            $data['menus'] = Menu::list($data['branch_id'], true);
            $data['menu_id'] = $data['menus'][0]['id'] ?? null;

            $data['registers'] = PosRegister::list($data['branch_id'], true);
            $data['register_id'] = $data['registers'][0]['id'] ?? null;
            $data['session_id'] = $data['registers'][0]['session']['id'] ?? null;

            /** @var PrintAgent $agent */
            $agent = PrintAgent::whereBranch($data['branch_id'])
                ->withOutGlobalBranchPermission()
                ->whereNotNull('api_key')
                ->whereNotNull('host')
                ->whereNotNull('port')
                ->first();

            $data['agent'] = $agent ? [
                'id' => $agent->id,
                'name' => $agent->name,
                'api_key' => $agent->api_key,
                'base_url' => $agent->getBaseUrl()
            ] : null;
        }

        $data['cart'] = Cart::instance();
        $data['menu_items'] = !is_null($data['menu_id'])
            ? $this->getMenuItems($data['menu_id'])
            : [
                "categories" => [],
                "products" => [],
            ];

        return $data;
    }

    /** @inheritDoc */
    public function getMenuItems(int $menuId): array
    {
        return [
            "categories" => $this->getCategories($menuId),
            "products" => $this->getProducts($menuId),
        ];
    }

    /** @inheritDoc */
    public function getCategories(int $menuId): AnonymousResourceCollection
    {
        return Cache::tags("categories")
            ->rememberForever(
                makeCacheKey([
                    'categories',
                    "menu-$menuId",
                    'pos_tree'
                ]),
                fn() => PosCategoryResource::collection(
                    Category::query()
                        // color_hue agregado al select Sprint 2 fix: el transformer
                        // lo expone y Laravel 12 tira "attribute does not exist" si
                        // no está en el select explícito. Sin color hex — decisión
                        // opción A: color_hue es la fuente de verdad, el frontend
                        // deriva HSL(hue 55% 50%) para todo uso visual.
                        ->select(['id', 'name', 'parent_id', 'order', 'menu_id', 'color_hue'])
                        ->with(["childrenRecursive", "files"])
                        ->whereNull("parent_id")
                        ->whereMenu($menuId)
                        ->withOutGlobalBranchPermission()
                        ->orderBy("order")
                        ->get()
                ));
    }

    /** @inheritDoc */
    public function getProducts(int $menuId): AnonymousResourceCollection
    {
        return Cache::tags(["products", "categories"])
            ->rememberForever(
                makeCacheKey([
                    'products',
                    "menu-$menuId",
                    'pos'
                ]),
                fn() => PosProductResource::collection(
                    Product::query()
                        ->with([
                            "files",
                            "categories:id,menu_id",
                            "options" => fn($query) => $query->with("values")
                        ])
                        ->whereHas(
                            'categories',
                            fn($query) => $query->whereIn('id', Category::getFullyActiveCategoryIds($menuId))
                        )
                        ->whereMenu($menuId)
                        ->latest()
                        ->get())
            );
    }
}
