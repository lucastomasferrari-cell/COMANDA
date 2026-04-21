<?php

namespace Modules\Dashboard\Services\Dashboard;

use Carbon\Carbon;
use DB;
use Modules\Category\Models\Category;
use Modules\Currency\Currency;
use Modules\Dashboard\Enums\AnalyticsPeriod;
use Modules\Dashboard\Enums\SalesAnalyticsFilter;
use Modules\Inventory\Models\Ingredient;
use Modules\Menu\Models\Menu;
use Modules\Order\Enums\OrderType;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderProduct;
use Modules\Payment\Enums\PaymentMethod;
use Modules\Payment\Models\Payment;
use Modules\Pos\Models\PosCashMovement;
use Modules\Product\Models\Product;
use Modules\Support\Enums\Day;
use Modules\Support\Enums\Monthly;
use Modules\Support\Money;
use Modules\Support\RTLDetector;
use Modules\User\Models\User;

class DashboardService implements DashboardServiceInterface
{
    /** @inheritDoc */
    public function overview(): array
    {
        $user = auth()->user();
        $currency = $user->assignedToBranch() ? $user->branch->currency : setting("default_currency");

        return [
            "total_sales" => $this->callIfAuthorized(
                "admin.dashboards.total_sales",
                fn() => Order::totalSales($currency)->format()
            ),
            "total_orders" => $this->callIfAuthorized(
                "admin.dashboards.total_orders",
                fn() => Order::withoutCanceledOrders()->count()
            ),
            "total_active_orders" => $this->callIfAuthorized(
                "admin.dashboards.total_active_orders",
                fn() => Order::activeOrders()->count()
            ),
            "average_order_value" => $this->callIfAuthorized(
                "admin.dashboards.average_order_value",
                fn() => Order::averageOrderValue($currency)->format()
            ),
            "total_users" => $this->callIfAuthorized(
                "admin.dashboards.total_users",
                fn() => User::withoutGlobalActive()->count()
            ),
            "total_menus" => $this->callIfAuthorized(
                "admin.dashboards.total_menus",
                fn() => Menu::withoutGlobalActive()->count()
            ),
            "total_products" => $this->callIfAuthorized(
                "admin.dashboards.total_products",
                fn() => Product::whereHas("menu", fn($query) => $query->where("is_active", true))
                    ->withoutGlobalActive()
                    ->count()
            ),
            "total_categories" => $this->callIfAuthorized(
                "admin.dashboards.total_categories",
                fn() => Category::whereHas("menu", fn($query) => $query->where("is_active", true))
                    ->withoutGlobalActive()
                    ->count()
            )
        ];
    }

    /**
     * Execute a callback only if the authenticated user has the given permission.
     *
     * @param string $permission
     * @param callable $callback
     * @return mixed|null
     */
    private function callIfAuthorized(string $permission, callable $callback): mixed
    {
        return auth()->user()->can($permission) ? $callback() : null;
    }

    /** @inheritDoc */
    public function salesAnalytics(SalesAnalyticsFilter $filter): array
    {
        $user = auth()->user();
        $withRate = !$user->assignedToBranch();
        $currency = $withRate ? setting("default_currency") : $user->branch->currency;
        $query = Order::query()
            ->withoutCanceledOrders()
            ->orderBy('created_at');

        $scale = Currency::subunit($currency);

        if ($filter === SalesAnalyticsFilter::Weekly) {
            $startOfWeek = startOfWeek();
            $endOfWeek = endOfWeek();
            $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);

            $rawResults = $query
                ->selectRaw("DATE(created_at) as date")
                ->when(
                    $withRate,
                    fn($q) => $q->addSelect(DB::raw('SUM(total * currency_rate) as total_sales')),
                    fn($q) => $q->addSelect(DB::raw('SUM(total * currency_rate) as total_sales'))
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get()
                ->mapWithKeys(fn($row) => [
                    Carbon::parse($row->date)->englishDayOfWeek => round($row->total_sales, $scale),
                ]);


            $days = collect();

            for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
                $days->push(Day::from(strtolower($date->englishDayOfWeek))->trans());
            }

            $results = $days->map(fn($day) => [
                'label' => __("support::enums.days." . strtolower($day)),
                'total_sales' => $rawResults[$day] ?? 0,
            ]);
        } elseif ($filter === SalesAnalyticsFilter::Monthly) {
            $orders = $query
                ->select([
                    'created_at',
                    DB::raw('total as total_sales'),
                    'currency_rate'
                ])
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->get();

            if ($orders->isEmpty()) {
                return ['labels' => [], 'data' => []];
            }

            // Default zeroed week values
            $weekLabels = [
                Monthly::Week1->trans(),
                Monthly::Week2->trans(),
                Monthly::Week3->trans(),
                Monthly::Week4->trans(),
            ];

            $totals = collect(array_fill_keys($weekLabels, 0));

            foreach ($orders as $order) {
                $day = Carbon::parse($order->created_at)->day;
                $week = match (true) {
                    $day <= 7 => Monthly::Week1->trans(),
                    $day <= 14 => Monthly::Week2->trans(),
                    $day <= 21 => Monthly::Week3->trans(),
                    default => Monthly::Week4->trans(),
                };

                $totals[$week] += $order->total_sales * ($withRate ? $order->currency_rate : 1);
            }

            $results = $totals->map(fn($total, $label) => [
                'label' => $label,
                'total_sales' => round($total, $scale),
            ])->values();
        } else {
            return ['labels' => [], 'data' => []];
        }

        return [
            'labels' => $results
                ->when(RTLDetector::detect(), fn($results) => $results->reverse())
                ->pluck('label')->all(),
            'data' => $results
                ->when(RTLDetector::detect(), fn($results) => $results->reverse())
                ->pluck('total_sales')->all(),
            "currency" => $currency
        ];
    }

    /** @inheritDoc */
    public function bestPerformingBranches(AnalyticsPeriod $filter, int $limit = 5): array
    {
        $withRate = !auth()->user()?->assignedToBranch();

        $query = Order::query()
            ->withoutCanceledOrders()
            ->select([
                'branch_id',
                DB::raw($withRate
                    ? 'SUM(total * currency_rate) as total_sales'
                    : 'SUM(total) as total_sales'),
                DB::raw('COUNT(id) as total_orders'),
            ])
            ->groupBy('branch_id')
            ->orderByDesc('total_sales')
            ->with('branch:id,name,currency');

        // Apply date filters
        match ($filter) {
            AnalyticsPeriod::Today => $query->whereDate('created_at', today()),
            AnalyticsPeriod::ThisWeek => $query->whereBetween('created_at', [startOfWeek(), endOfWeek()]),
            AnalyticsPeriod::ThisMonth => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            AnalyticsPeriod::ThisYear => $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]),
            AnalyticsPeriod::AllTime => null, // No filter
        };

        $branches = $query->limit($limit)->get();

        return $branches->map(fn($order) => [
            'branch_id' => $order->branch_id,
            'branch_name' => $order->branch->name,
            'total_orders' => $order->total_orders,
            'total_sales' => $withRate
                ? Money::inDefaultCurrency($order->total_sales)
                : (new Money($order->total_sales, $order->branch->currency)),
        ])->toArray();
    }

    /** @inheritDoc */
    public function orderTypeDistribution(AnalyticsPeriod $filter): array
    {
        $query = Order::query()
            ->withoutCanceledOrders()
            ->select('type', DB::raw('COUNT(*) as total_orders'));

        // Filter by date range based on the selected period
        match ($filter) {
            AnalyticsPeriod::Today => $query->whereDate('created_at', now()),
            AnalyticsPeriod::ThisWeek => $query->whereBetween('created_at', [startOfWeek(), endOfWeek()]),
            AnalyticsPeriod::ThisMonth => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            AnalyticsPeriod::ThisYear => $query->whereYear('created_at', now()->year),
            default => null,
        };

        $results = $query
            ->groupBy('type')
            ->orderByDesc('total_orders')
            ->get();

        $types = $results->pluck('type');
        return [
            'labels' => $types->map(fn($type) => $type->trans())->all(),
            'data' => $results->pluck('total_orders')->all(),
            'colors' => $types->map(fn($type) => OrderType::getColor($type->value))->all(),
        ];
    }

    /** @inheritDoc */
    public function orderTotalByStatus(AnalyticsPeriod $filter): array
    {
        $query = Order::query()
            ->select('status', DB::raw('COUNT(*) as total_orders'));

        // Filter by date range based on the selected period
        match ($filter) {
            AnalyticsPeriod::Today => $query->whereDate('created_at', now()),
            AnalyticsPeriod::ThisWeek => $query->whereBetween('created_at', [startOfWeek(), endOfWeek()]),
            AnalyticsPeriod::ThisMonth => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            AnalyticsPeriod::ThisYear => $query->whereYear('created_at', now()->year),
            default => null,
        };

        $results = $query
            ->groupBy('status')
            ->orderByDesc('total_orders')
            ->get();

        $statuses = $results->pluck('status');
        return [
            'labels' => $statuses->map(fn($status) => $status->trans())->all(),
            'data' => $results->pluck('total_orders')->all(),
            'colors' => $statuses->map(fn($status) => $status->color())->all(),
        ];
    }

    /** @inheritDoc */
    public function paymentsOverview(AnalyticsPeriod $filter): array
    {
        $user = auth()->user();
        $withRate = !$user->assignedToBranch();
        $currency = $withRate ? setting("default_currency") : $user->branch->currency;
        $precision = Currency::subunit($currency);
        $query = Payment::query()
            ->whereHas("order", fn($query) => $query->withoutCanceledOrders())
            ->select([
                'method',
                DB::raw($withRate
                    ? 'SUM(amount * currency_rate) as total_amount'
                    : 'SUM(amount) as total_amount')
            ])
            ->groupBy('method');

        // Apply period filter
        match ($filter) {
            AnalyticsPeriod::Today => $query->whereDate('created_at', today()),
            AnalyticsPeriod::ThisWeek => $query->whereBetween('created_at', [startOfWeek(), endOfWeek()]),
            AnalyticsPeriod::ThisMonth => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            AnalyticsPeriod::ThisYear => $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]),
            AnalyticsPeriod::AllTime => null,
        };

        $results = $query->get()->map(fn($row) => [
            'label' => $row->method->trans(),
            'total_amount' => round($row->total_amount, $precision),
            "color" => PaymentMethod::getColor($row->method->value)
        ]);

        return [
            "labels" => $results->pluck("label")->all(),
            "data" => $results->pluck("total_amount")->all(),
            "colors" => $results->pluck("color")->all(),
            "currency" => $currency,
        ];
    }

    /** @inheritDoc */
    public function hourlySalesTrend(): array
    {
        $user = auth()->user();
        $withRate = !$user->assignedToBranch();
        $currency = $withRate ? setting("default_currency") : $user->branch->currency;
        $precision = Currency::subunit($currency);
        // Base query
        $query = Order::query()
            ->withoutCanceledOrders()
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderBy('created_at');

        // Fetch hourly data
        $results = $query
            ->when(
                $withRate,
                fn($q) => $q->selectRaw('HOUR(created_at) as hour, SUM(total * currency_rate) as total_sales'),
                fn($q) => $q->selectRaw('HOUR(created_at) as hour, SUM(total) as total_sales'),
            )
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->pluck('total_sales', 'hour');

        // Initialize 24 hours with 0
        $hours = collect(range(0, 23))->mapWithKeys(function ($hour) use ($results, $precision) {
            return [$hour => round($results[$hour] ?? 0, $precision)];
        });

        return [
            'labels' => $hours
                ->when(RTLDetector::detect(), fn($results) => $results->reverse())
                ->keys()
                ->map(fn($h) => Carbon::parse(str_pad($h, 2, '0', STR_PAD_LEFT) . ':00')
                    ->format(setting("default_time_format")))
                ->toArray(),
            'data' => $hours
                ->when(RTLDetector::detect(), fn($results) => $results->reverse())
                ->values()
                ->toArray(),
            "currency" => $currency
        ];
    }

    /** @inheritDoc */
    public function branchWiseSalesComparison(AnalyticsPeriod $filter): array
    {
        $query = Order::query()
            ->withoutCanceledOrders()
            ->with('branch:id,name,currency')
            ->select(['branch_id', 'currency'])
            ->selectRaw('SUM(total * currency_rate) as total_sales')
            ->groupBy('branch_id')
            ->orderByDesc('total_sales');

        // Apply period filter
        match ($filter) {
            AnalyticsPeriod::Today => $query->whereDate('created_at', today()),
            AnalyticsPeriod::ThisWeek => $query->whereBetween('created_at', [startOfWeek(), endOfWeek()]),
            AnalyticsPeriod::ThisMonth => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            AnalyticsPeriod::ThisYear => $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]),
            AnalyticsPeriod::AllTime => null,
        };

        $results = $query->get();

        return [
            'labels' => $results->pluck('branch.name')->toArray(),
            'data' => $results->map(fn($row) => round($row->total_sales, Currency::subunit($row->currency)))->toArray(),
            "currency" => setting("default_currency"),
        ];
    }

    /** @inheritDoc */
    public function cashMovementsOverview(AnalyticsPeriod $filter): array
    {
        $user = auth()->user();
        $withRate = !$user->assignedToBranch();
        $currency = $withRate ? setting("default_currency") : $user->branch->currency;
        $precision = Currency::subunit($currency);
        $query = PosCashMovement::query()
            ->select([
                'direction',
                DB::raw(
                    $withRate
                        ? 'SUM(amount * currency_rate) as total'
                        : 'SUM(amount) as total'
                ),
            ])
            ->groupBy('direction');

        // Apply date filters
        match ($filter) {
            AnalyticsPeriod::Today => $query->whereDate('created_at', today()),
            AnalyticsPeriod::ThisWeek => $query->whereBetween('created_at', [startOfWeek(), endOfWeek()]),
            AnalyticsPeriod::ThisMonth => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            AnalyticsPeriod::ThisYear => $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]),
            AnalyticsPeriod::AllTime => null,
        };

        $results = $query->get()->map(fn($movement) => [
            'label' => $movement->direction->trans(),
            'total' => round($movement->total, $precision),
            'color' => $movement->direction->color(),
        ]);

        return [
            'labels' => $results->pluck('label')->all(),
            'data' => $results->pluck('total')->all(),
            'colors' => $results->pluck('color')->all(),
            'currency' => $currency
        ];
    }

    /** @inheritDoc */
    public function topSellingProducts(AnalyticsPeriod $filter, int $limit = 5): array
    {
        $withRate = !auth()->user()?->assignedToBranch();

        $query = OrderProduct::query()
            ->with('product', fn($query) => $query->with("files"))
            ->whereHas('order', fn($q) => $q->withoutCanceledOrders());

        // Apply period filter
        match ($filter) {
            AnalyticsPeriod::Today => $query->whereDate('created_at', today()),
            AnalyticsPeriod::ThisWeek => $query->whereBetween('created_at', [startOfWeek(), endOfWeek()]),
            AnalyticsPeriod::ThisMonth => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            AnalyticsPeriod::ThisYear => $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]),
            AnalyticsPeriod::AllTime => null,
        };

        $results = $query->get()
            ->groupBy('product_id')
            ->map(function ($items) use ($withRate) {
                $firstItem = $items->first();
                $product = $firstItem->product;
                $totalSales = $items->sum(fn($item) => $item->total->amount() * ($withRate ? $item->currency_rate : 1));

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    "thumbnail" => $product->thumbnail != null ? $product->thumbnail->preview_image_url : null,
                    'total_quantity' => $items->sum('quantity'),
                    'total_sales' => $withRate
                        ? Money::inDefaultCurrency($totalSales)
                        : new Money($totalSales, $firstItem->currency),
                ];
            })
            ->sortByDesc('total_quantity')
            ->take($limit)
            ->values();

        return $results->toArray();
    }

    /**
     * Get low stock ingredients
     *
     * @return array
     */
    public function getLowStockAlerts(): array
    {
        return Ingredient::query()
            ->whereColumn('current_stock', '<', 'alert_quantity')
            ->orderBy('current_stock')
            ->get()
            ->map(fn(Ingredient $ingredient) => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'current_stock' => $ingredient->current_stock,
                'alert_quantity' => $ingredient->alert_quantity,
                'symbol' => strtoupper($ingredient->unit->symbol)
            ])
            ->toArray();
    }
}
