<?php

namespace Modules\Dashboard\Services\Dashboard;

use Modules\Dashboard\Enums\AnalyticsPeriod;
use Modules\Dashboard\Enums\SalesAnalyticsFilter;

interface DashboardServiceInterface
{
    /**
     * Get dashboard overview data
     *
     * @return array
     */
    public function overview(): array;

    /**
     * Pulse: snapshot ligero de hoy para el navbar.
     * Devuelve sales_today, orders_today, orders_active (pendientes + en preparacion).
     *
     * @return array
     */
    public function pulse(): array;

    /**
     * Get dashboard best performing branches
     *
     * @param AnalyticsPeriod $filter
     * @param int $limit
     * @return array
     */
    public function bestPerformingBranches(AnalyticsPeriod $filter, int $limit = 5): array;

    /**
     * Get dashboard order type distribution
     *
     * @param AnalyticsPeriod $filter
     * @return array
     */
    public function orderTypeDistribution(AnalyticsPeriod $filter): array;

    /**
     * Get sales analytics
     *
     * @param SalesAnalyticsFilter $filter
     * @return array
     */
    public function salesAnalytics(SalesAnalyticsFilter $filter): array;

    /**
     * Order total by status
     *
     * @param AnalyticsPeriod $filter
     * @return array
     */
    public function orderTotalByStatus(AnalyticsPeriod $filter): array;

    /**
     * Payments Overview
     *
     * @param AnalyticsPeriod $filter
     * @return array
     */
    public function paymentsOverview(AnalyticsPeriod $filter): array;

    /**
     * Branch wise sales comparison
     *
     * @param AnalyticsPeriod $filter
     * @return array
     */
    public function branchWiseSalesComparison(AnalyticsPeriod $filter): array;

    /**
     * Cash Movements Overview
     *
     * @param AnalyticsPeriod $filter
     * @return array
     */
    public function cashMovementsOverview(AnalyticsPeriod $filter): array;

    /**
     * Top a selling products
     *
     * @param AnalyticsPeriod $filter
     * @param int $limit
     * @return array
     */
    public function topSellingProducts(AnalyticsPeriod $filter, int $limit = 5): array;

    /**
     * Get hourly sales trend
     *
     * @return array
     */
    public function hourlySalesTrend(): array;

    /**
     * Get low stock alerts
     *
     * @return array
     */
    public function getLowStockAlerts(): array;

}
