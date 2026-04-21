<?php

namespace Modules\Pos\Services\KitchenViewer;

use Arr;
use DB;
use Modules\Branch\Models\Branch;
use Modules\Category\Models\Category;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\OrderType;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderProduct;
use Modules\User\Models\User;

class KitchenViewerService implements KitchenViewerServiceInterface
{
    /** @inheritDoc */
    public function getOrders(?int $branchId = null): array
    {
        $user = auth()->user();
        $branchId ??= $user->effective_branch?->id;

        $categoryIds = $this->resolveKitchenCategoryIds($user);

        $baseQuery = Order::query()
            ->where('branch_id', $branchId)
            ->forKitchenCategories($categoryIds)
            ->visibleForKitchen()
            ->activeOrders()
            ->whereIn('status', [
                OrderStatus::Confirmed,
                OrderStatus::Preparing,
                OrderStatus::Ready,
                OrderStatus::Served,
            ]);

        return [
            'orders' => (clone $baseQuery)
                ->with([
                    'table:id,name',
                    'products' => fn($q) => $q->when(
                        count($categoryIds),
                        fn($q) => $q->whereHas(
                            'product.categories',
                            fn($q) => $q->whereIn('id', $categoryIds)
                        )
                    )
                ])
                ->orderByDesc('updated_at')
                ->get(),
            'last_updated_at' => optional((clone $baseQuery)->max('updated_at'))?->toISOString(),
        ];
    }


    /**
     * Resolve kitchen category ids
     * @param User $user
     * @return array
     */
    private function resolveKitchenCategoryIds(User $user): array
    {
        if (empty($user->category_slugs)) {
            return [];
        }

        return Category::query()
            ->whereIn('slug', $user->category_slugs)
            ->get()
            ->flatMap(fn($category) => $category->descendants()->pluck('id'))
            ->unique()
            ->values()
            ->all();
    }

    /** @inheritDoc */
    public function getConfiguration(?int $branchId = null): array
    {
        $user = auth()->user();
        $branch = null;

        $data = [
            "branches" => [],
            "order_types" => [],
            "branch_id" => $user->assignedToBranch() ? $user->branch_id : $branchId,
        ];

        if (is_null($data['branch_id'])) {
            if ($user->assignedToBranch()) {
                $data['branches'][] = [
                    "id" => $user->branch->id,
                    "name" => $user->branch->name,
                    "currency" => $user->branch->currency,
                ];
                $branch = $user->branch;
            } else {
                $data['branches'] = Branch::list();
            }

            $data['settings'] = [
                "auto_refresh" => [
                    "enabled" => setting('auto_refresh_enabled'),
                    ...(setting('auto_refresh_enabled') ? [
                        "mode" => setting('auto_refresh_mode'),
                        "interval" => setting('auto_refresh_interval'),
                        "pause_on_idle" => setting('auto_refresh_pause_on_idle'),
                        "idle_timeout" => setting('auto_refresh_idle_timeout'),
                    ] : [])
                ]
            ];
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

            $data['order_types'] = array_values(array_filter(
                OrderType::toArrayTrans(),
                fn($orderType) => in_array($orderType['id'], ($branch->order_types ?: []))
            ));
        }

        return $data;
    }

    /** @inheritDoc */
    public function moveOrderProductToNextStatus(int|string $orderId, array|int $ids): void
    {

        $order = Order::query()
            ->where(fn($query) => $query->where('id', $orderId)
                ->orWhere('reference_no', $orderId))
            ->whereNot('status', OrderStatus::Served)
            ->activeOrders()
            ->firstOrFail();
        $orderProducts = $order->products()->whereIn('id', Arr::wrap($ids))->get();

        if (count($orderProducts)) {
            DB::transaction(function () use ($order, $orderProducts, $ids) {

                /** @var OrderProduct $orderProduct */
                foreach ($orderProducts as $orderProduct) {
                    $nextStatus = $orderProduct->status->nextStatus();

                    if ($nextStatus === null) {
                        continue;
                    }

                    $orderProduct->update(['status' => $nextStatus]);
                }

                $order->recalculateOrderStatus();
            });
        }
    }
}
