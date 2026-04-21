<?php

namespace Modules\Inventory\Services\InventoryAnalytics;

use DB;
use Illuminate\Support\Carbon;
use Modules\Branch\Models\Branch;
use Modules\Inventory\Enums\StockMovementType;
use Modules\Inventory\Models\Ingredient;
use Modules\Inventory\Models\Purchase;
use Modules\Inventory\Models\PurchaseItem;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Supplier;
use Modules\Support\Enums\DateTimeFormat;

class InventoryAnalyticsService implements InventoryAnalyticsServiceInterface
{
    /** @inheritDoc */
    public function topSuppliers(?string $from, ?string $to, ?int $branchId): array
    {
        $user = auth()->user();
        $isBranchUser = $user->assignedToBranch();
        $branch = $isBranchUser ? Branch::find($user->branch_id) : null;

        if ($isBranchUser) {
            $branchId = $user->branch_id;
        }

        $query = Supplier::query()
            ->select('suppliers.name')
            ->join('purchases', 'suppliers.id', '=', 'purchases.supplier_id')
            ->when($branchId, fn($q) => $q->where('purchases.branch_id', $branchId))
            ->when($from, fn($q) => $q->whereDate('purchases.created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('purchases.created_at', '<=', $to))
            ->groupBy('suppliers.name');

        if ($isBranchUser) {
            $query->selectRaw('SUM(purchases.total) as total_amount');
        } else {
            $query->selectRaw('SUM(purchases.total * purchases.currency_rate) as total_amount');
        }

        $data = $query
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        $colors = [
            'rgba(45, 156, 219, 0.6)', 'rgba(39, 174, 96, 0.6)',
            'rgba(243, 156, 18, 0.6)', 'rgba(231, 76, 60, 0.6)',
            'rgba(142, 68, 173, 0.6)', 'rgba(26, 188, 156, 0.6)',
            'rgba(241, 196, 15, 0.6)', 'rgba(149, 165, 166, 0.6)',
            'rgba(230, 126, 34, 0.6)', 'rgba(52, 73, 94, 0.6)',
        ];

        return [
            'currency' => $branch?->currency ?? setting('default_currency'),
            'labels' => $data->pluck('name'),
            'datasets' => [
                [
                    'label' => __("inventory::inventories.analytics.top_suppliers"),
                    'data' => $data->pluck('total_amount'),
                    'backgroundColor' => $data->keys()->map(fn($i) => $colors[$i % count($colors)]),
                ]
            ]
        ];
    }

    /** @inheritDoc */
    public function ingredientPurchases(?string $from, ?string $to, ?int $branchId): array
    {
        $user = auth()->user();

        if ($user->assignedToBranch()) {
            $branchId = $user->branch_id;
        }

        $data = PurchaseItem::query()
            ->select('ingredient_id')
            ->selectRaw('SUM(purchase_items.quantity) as total_quantity')
            ->join('ingredients', 'ingredients.id', '=', 'purchase_items.ingredient_id')
            ->join('units', 'units.id', '=', 'ingredients.unit_id')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->when($branchId, fn($q) => $q->where('purchases.branch_id', $branchId))
            ->when($from, fn($q) => $q->whereDate('purchases.created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('purchases.created_at', '<=', $to))
            ->groupBy('ingredient_id', 'ingredients.name', 'units.symbol')
            ->orderByDesc('total_quantity')
            ->get();

        return $data->map(fn($item) => [
            "id" => $item->ingredient_id,
            "name" => $item->ingredient->name,
            "total_quantity" => (float)$item->total_quantity . " " . ucfirst($item->ingredient->unit->symbol),
        ])->toArray();
    }

    /** @inheritDoc */
    public function stockMovementSummary(?string $from, ?string $to, ?int $branchId): array
    {
        $user = auth()->user();
        if ($user->assignedToBranch()) {
            $branchId = $user->branch_id;
        }

        $data = StockMovement::query()
            ->select('type')
            ->selectRaw('SUM(quantity) as total')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->groupBy('type')
            ->get();

        $colors = [
            'in' => 'rgba(46, 204, 113, 0.6)',
            'out' => 'rgba(255, 99, 132, 0.6)',
            'spoil' => 'rgba(241, 196, 15, 0.6)',
            'adjust_add' => 'rgba(52, 152, 219, 0.6)',
            'adjust_subtract' => 'rgba(155, 89, 182, 0.6)',
            'transfer_in' => 'rgba(26, 188, 156, 0.6)',
            'transfer_out' => 'rgba(52, 73, 94, 0.6)',
            'return_supplier' => 'rgba(230, 126, 34, 0.6)',
            'sample' => 'rgba(127, 140, 141, 0.6)',
            'waste' => 'rgba(255, 159, 64, 0.6)',
        ];

        return [
            'labels' => $data->map(fn($item) => $item->type->trans()),
            'datasets' => [
                [
                    'label' => __("inventory::inventories.analytics.stock_movements"),
                    'data' => $data->pluck('total'),
                    'backgroundColor' => $data->pluck('type')->map(fn($type) => $colors[$type->value] ?? 'rgba(189, 195, 199, 0.6)'),
                ]
            ]
        ];
    }

    /** @inheritDoc */
    public function wastageAndSpoilage(?string $from, ?string $to, ?int $branchId): array
    {
        $user = auth()->user();
        $isBranchUser = $user->assignedToBranch();

        $data = StockMovement::query()
            ->select('type')
            ->selectRaw('SUM(quantity) as total')
            ->whereIn('type', ['waste', 'spoil'])
            ->when(!$isBranchUser && $branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->groupBy('type')
            ->get();

        return [
            'labels' => $data->map(fn($item) => $item->type->trans()),
            'datasets' => [
                [
                    'label' => __("inventory::inventories.analytics.wastage_and_spoilage"),
                    'data' => $data->pluck('total'),
                    'backgroundColor' => [
                        'rgba(241, 196, 15, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                    ],
                ]
            ]
        ];
    }

    /** @inheritDoc */
    public function purchaseStatusSummary(?string $from, ?string $to, ?int $branchId): array
    {
        $user = auth()->user();

        if ($user->assignedToBranch()) {
            $branchId = $user->branch_id;
        }

        $data = Purchase::query()
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->groupBy('status')
            ->get();

        $colors = [
            'draft' => 'rgba(127, 140, 141, 0.6)',
            'pending' => 'rgba(52, 152, 219, 0.6)',
            'partially_received' => 'rgba(255, 159, 64, 0.6)',
            'received' => 'rgba(46, 204, 113, 0.6)',
            'cancelled' => 'rgba(231, 76, 60, 0.6)',
        ];

        return [
            'labels' => $data->map(fn($item) => $item->status->trans()),
            'datasets' => [
                [
                    'label' => __("inventory::inventories.analytics.purchase_status"),
                    'data' => $data->pluck('count'),
                    'backgroundColor' => $data->pluck('status')->map(fn($status) => $colors[$status->value] ?? 'rgba(189, 195, 199, 0.6)')
                ]
            ]
        ];
    }

    /** @inheritDoc */
    public function getMetaData(): array
    {
        $user = auth()->user();
        $isBranchUser = $user->assignedToBranch();

        $data = [];
        if (!$isBranchUser) {
            $data["branches"] = Branch::list();
        }

        return $data;
    }

    /** @inheritDoc */
    public function lowStockIngredients(?int $branchId = null): array
    {
        $user = auth()->user();
        if ($user->assignedToBranch()) {
            $branchId = $user->branch_id;
        }

        $ingredients = Ingredient::query()
            ->select('id', 'name', 'unit_id', 'current_stock', 'alert_quantity')
            ->with('unit')
            ->whereNotNull('alert_quantity')
            ->whereColumn('current_stock', '<=', 'alert_quantity')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->limit(10)->get();

        return $ingredients->map(fn($ingredient) => [
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'current_stock' => $ingredient->current_stock . ' ' . $ingredient->unit->symbol,
            'alert_quantity' => $ingredient->alert_quantity . ' ' . $ingredient->unit->symbol,
        ])->toArray();
    }

    /** @inheritDoc */
    public function fastMovingIngredients(?string $from = null, ?string $to = null, ?int $branchId = null): array
    {
        $user = auth()->user();

        if ($user->assignedToBranch()) {
            $branchId = $user->branch_id;
        }

        $to = $to ?: now()->toDateString();
        $from = $from ?: now()->subDays(30)->toDateString();

        $topIngredients = StockMovement::query()
            ->select('ingredient_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('type', StockMovementType::Out->value)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->groupBy('ingredient_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->pluck('ingredient_id');

        $data = StockMovement::query()
            ->select(
                'ingredient_id',
                DB::raw('DATE(stock_movements.created_at) as date'),
                'ingredients.name',
                DB::raw('SUM(quantity) as total')
            )
            ->join('ingredients', 'ingredients.id', '=', 'stock_movements.ingredient_id')
            ->where('type', 'out')
            ->whereIn('ingredient_id', $topIngredients)
            ->when($branchId, fn($q) => $q->where('stock_movements.branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(stock_movements.created_at)'), [$from, $to])
            ->groupBy('date', 'ingredient_id', 'ingredients.name')
            ->orderBy('date')
            ->get();

        $grouped = $data->groupBy('name');

        $labels = collect();
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        while ($start <= $end) {
            $labels->push(dateTimeFormat($start, DateTimeFormat::Date));
            $start->addDay();
        }

        $colors = [
            'rgba(45, 156, 219, 0.6)',
            'rgba(39, 174, 96, 0.6)',
            'rgba(243, 156, 18, 0.6)',
            'rgba(231, 76, 60, 0.6)',
            'rgba(142, 68, 173, 0.6)',
            'rgba(26, 188, 156, 0.6)',
            'rgba(241, 196, 15, 0.6)',
            'rgba(149, 165, 166, 0.6)',
            'rgba(230, 126, 34, 0.6)',
            'rgba(52, 73, 94, 0.6)',
        ];

        $index = 0;

        $datasets = $grouped->map(function ($entries, $name) use ($labels, $colors, &$index) {
            $color = $colors[$index % count($colors)];
            $daily = $entries->keyBy('date');

            $dataset = [
                'label' => $name,
                'fill' => false,
                'data' => $labels->map(fn($date) => (float)($daily[$date]->total ?? 0)),
                'backgroundColor' => $color,
                'borderColor' => $color,
                "borderWidth" => 2,
                "tension" => 0.3,
            ];

            $index++;
            return $dataset;
        })->values();

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /** @inheritDoc */
    public function mostWastedIngredients(?string $from = null, ?string $to = null, ?int $branchId = null): array
    {
        $user = auth()->user();
        if ($user->assignedToBranch()) {
            $branchId = $user->branch_id;
        }

        $data = StockMovement::query()
            ->select('ingredients.id', 'units.symbol as symbol', 'ingredients.name', DB::raw('SUM(stock_movements.quantity) as total_wasted'))
            ->join('ingredients', 'ingredients.id', '=', 'stock_movements.ingredient_id')
            ->join('units', 'units.id', '=', 'ingredients.unit_id')
            ->whereIn('stock_movements.type', [
                StockMovementType::Waste->value,
                StockMovementType::Spoil->value
            ])
            ->when($branchId, fn($q) => $q->where('stock_movements.branch_id', $branchId))
            ->when($from, fn($q) => $q->whereDate('stock_movements.created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('stock_movements.created_at', '<=', $to))
            ->groupBy('ingredients.id', 'ingredients.name', 'units.symbol')
            ->orderByDesc('total_wasted')
            ->limit(5)
            ->get();

        $colors = [
            'rgba(231, 76, 60, 0.6)',
            'rgba(243, 156, 18, 0.6)',
            'rgba(192, 57, 43, 0.6)',
            'rgba(211, 84, 0, 0.6)',
            'rgba(127, 140, 141, 0.6)',
        ];

        return [
            'labels' => $data->pluck('name'),
            'unitSymbols' => $data->pluck('symbol')->map(fn($symbol) => ucfirst($symbol)),
            'datasets' => [
                [
                    'label' => __("inventory::inventories.analytics.most_wasted_ingredients"),
                    'data' => $data->pluck('total_wasted'),
                    'backgroundColor' => $data->keys()->map(fn($i) => $colors[$i % count($colors)]),
                ],
            ],
        ];
    }
}
