<?php

namespace Modules\Dashboard\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\Controller;
use Modules\Dashboard\Enums\AnalyticsPeriod;
use Modules\Dashboard\Enums\SalesAnalyticsFilter;
use Modules\Dashboard\Services\Dashboard\DashboardServiceInterface;
use Modules\Support\ApiResponse;

class DashboardController extends Controller
{
    /**
     * Create a new instance of DashboardController
     *
     * @param DashboardServiceInterface $service
     */
    public function __construct(protected DashboardServiceInterface $service)
    {
    }

    /**
     * Get dashboard overview
     *
     * @return JsonResponse
     */
    public function overview(): JsonResponse
    {
        return ApiResponse::success($this->service->overview());
    }

    /**
     * Get dashboard sales analytics
     *
     * @param SalesAnalyticsFilter $filter
     * @return JsonResponse
     */
    public function salesAnalytics(SalesAnalyticsFilter $filter): JsonResponse
    {
        return ApiResponse::success($this->service->salesAnalytics($filter));
    }

    /**
     * Get dashboard best performing branches
     *
     * @param AnalyticsPeriod $filter
     * @return JsonResponse
     */
    public function bestPerformingBranches(AnalyticsPeriod $filter): JsonResponse
    {
        return ApiResponse::success($this->service->bestPerformingBranches($filter));
    }

    /**
     * Get dashboard order type distribution
     *
     * @param AnalyticsPeriod $filter
     * @return JsonResponse
     */
    public function orderTypeDistribution(AnalyticsPeriod $filter): JsonResponse
    {
        return ApiResponse::success($this->service->orderTypeDistribution($filter));
    }

    /**
     * Get dashboard order total by status
     *
     * @param AnalyticsPeriod $filter
     * @return JsonResponse
     */
    public function orderTotalByStatus(AnalyticsPeriod $filter): JsonResponse
    {
        return ApiResponse::success($this->service->orderTotalByStatus($filter));
    }

    /**
     * Get dashboard payments overview
     *
     * @param AnalyticsPeriod $filter
     * @return JsonResponse
     */
    public function paymentsOverview(AnalyticsPeriod $filter): JsonResponse
    {
        return ApiResponse::success($this->service->paymentsOverview($filter));
    }

    /**
     * Get dashboard hourly sales trend
     *
     * @return JsonResponse
     */
    public function hourlySalesTrend(): JsonResponse
    {
        return ApiResponse::success($this->service->hourlySalesTrend());
    }

    /**
     * Get dashboard branch wise sales comparison
     *
     * @param AnalyticsPeriod $filter
     * @return JsonResponse
     */
    public function branchWiseSalesComparison(AnalyticsPeriod $filter): JsonResponse
    {
        return ApiResponse::success($this->service->branchWiseSalesComparison($filter));
    }

    /**
     * Get dashboard cash movements Overview
     *
     * @param AnalyticsPeriod $filter
     * @return JsonResponse
     */
    public function cashMovementsOverview(AnalyticsPeriod $filter): JsonResponse
    {
        return ApiResponse::success($this->service->cashMovementsOverview($filter));
    }

    /**
     * Get dashboard top selling products
     *
     * @param AnalyticsPeriod $filter
     * @return JsonResponse
     */
    public function topSellingProducts(AnalyticsPeriod $filter): JsonResponse
    {
        return ApiResponse::success($this->service->topSellingProducts($filter));
    }

    /**
     * Get low stock alerts
     *
     * @return JsonResponse
     */
    public function getLowStockAlerts(): JsonResponse
    {
        return ApiResponse::success($this->service->getLowStockAlerts());
    }

}
