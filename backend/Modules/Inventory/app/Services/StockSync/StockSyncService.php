<?php


namespace Modules\Inventory\Services\StockSync;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Enums\StockMovementType;
use Modules\Inventory\Services\StockMovement\StockMovementServiceInterface;
use Modules\Order\Enums\OrderProductStatus;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderProduct;
use Modules\Order\Models\OrderProductOption;
use Modules\Product\Models\Ingredientable;
use Modules\Product\Models\Product;
use Throwable;

class StockSyncService implements StockSyncServiceInterface
{
    /** @inheritDoc */
    public function deductOrderStock(Order $order): void
    {
        $this->sync($order);
    }

    /**
     * Sync
     *
     * @param Order $order
     * @param bool $isIn
     * @return void
     * @throws Throwable
     */
    private function sync(Order $order, bool $isIn = false): void
    {
        $order->load([
            "products" => fn($query) => $query->with([
                "product" => fn($query) => $query->with([
                    "ingredients" => fn($query) => $query
                        ->select("id", "ingredientable_id", "ingredientable_type", "quantity", "loss_pct", "ingredient_id")
                        ->with("ingredient:id,cost_per_unit,is_returnable,unit_id")
                ]),
                "options" => fn($query) => $query->with([
                    "values" => fn($query) => $query->with([
                        "ingredients" => fn($query) => $query
                            ->select("id", "ingredientable_id", "ingredientable_type", "operation", "quantity", "loss_pct", "ingredient_id")
                            ->with("ingredient:id,cost_per_unit,is_returnable,unit_id")
                    ])
                ]),
            ])
        ]);

        if (!$order->products->count()) return;

        DB::transaction(function () use ($order, $isIn) {
            $totalCostPrice = 0;
            $totalRevenue = 0;

            /** @var OrderProduct $orderProduct */
            foreach ($order->products as $orderProduct) {
                if ($orderProduct->status == OrderProductStatus::Cancelled) {
                    continue;
                }

                $lines = $this->resolveProductBom(
                    product: $orderProduct->product,
                    values: $orderProduct
                        ->options
                        ->map(fn(OrderProductOption $option) => $option->values)
                        ->flatMap
                        ->values(),
                    branchId: $order->branch_id
                );

                $totalOrderProductCostPrice = 0;

                foreach ($lines as $line) {
                    $quantity = (float)$line->quantity;

                    if (isset($line->loss_pct)) {
                        $quantity += $quantity * ((float)$line->loss_pct / 100);
                    }

                    $totalQuantity = $quantity * (float)$orderProduct->quantity;
                    $totalOrderProductCostPrice += $line->ingredient->cost_per_unit->amount() * $totalQuantity;

                    if ($isIn && !$line->ingredient->is_returnable) {
                        continue;
                    }

                    app(StockMovementServiceInterface::class)
                        ->store([
                            'branch_id' => $order->branch_id,
                            "ingredient_id" => $line->ingredient_id,
                            "type" => $isIn ? StockMovementType::In : StockMovementType::Out,
                            "source_id" => $order->id,
                            "source_type" => Order::class,
                            "quantity" => $totalQuantity,
                            "note" => $isIn
                                ? "Restocked due to order refund #$order->reference_no"
                                : "Deducted due to order #$order->reference_no",
                        ]);
                }

                if (!$isIn) {
                    $revenue = $orderProduct->subtotal->amount() - $totalOrderProductCostPrice;

                    $totalCostPrice += $totalOrderProductCostPrice;
                    $totalRevenue += $revenue;

                    $orderProduct->update([
                        "cost_price" => $totalOrderProductCostPrice,
                        "revenue" => $revenue,
                    ]);
                }
            }

            if (!$isIn) {
                $order->update([
                    "cost_price" => $totalCostPrice,
                    "revenue" => $totalRevenue,
                ]);
            }
        });
    }

    /** @inheritDoc */
    public function resolveProductBom(Product $product, Collection $values, int $branchId): Collection
    {
        $lines = $product->ingredients
            ->groupBy('ingredient_id')
            ->map(function ($group) {
                $first = $group->first()->replicate();
                $first->quantity = $group->sum('quantity');
                $first->loss_pct = $group->sum('loss_pct');
                return $first;
            })
            ->keyBy('ingredient_id');

        foreach ($values as $value) {
            $mods = $value->ingredients
                ->groupBy('ingredient_id')
                ->map(function ($group) {
                    $first = $group->first()->replicate();
                    $first->quantity = $group->sum('quantity');
                    $first->loss_pct = $group->sum('loss_pct');
                    return $first;
                });

            /** @var Ingredientable $m */
            foreach ($mods as $m) {
                $id = $m->ingredient_id;

                switch ($m->operation->value) {
                    case 'remove':
                        $lines->forget($id);
                        break;
                    case 'add':
                        if (!$lines->has($id)) {
                            $lines[$id] = $m;
                        } else {
                            $existing = $lines[$id];
                            $existing->quantity += $m->quantity;
                        }
                        break;

                    case 'subtract':
                        if ($lines->has($id)) {
                            $existing = $lines[$id];
                            $existing->quantity -= $m->quantity;
                            if ($existing->quantity <= 0) {
                                $lines->forget($id);
                            }
                        }
                        break;
                    case 'replace':
                        $lines[$id] = $m;
                        break;
                    case 'multiply':
                        if ($lines->has($id)) {
                            $existing = $lines[$id];
                            $existing->quantity *= $m->quantity;
                        }
                        break;
                }

                if (isset($m->loss_pct) && $lines->has($id)) {
                    $lines[$id]->loss_pct = (float)$lines[$id]->loss_pct + (float)$m->loss_pct;
                }
            }
        }

        return $lines;
    }

    /** @inheritDoc */
    public function restoreOrderStock(Order $order): void
    {
        $this->sync($order, true);
    }

}
