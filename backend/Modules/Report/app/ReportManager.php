<?php

namespace Modules\Report;

use Modules\Report\Reports\BranchPerformanceReport;
use Modules\Report\Reports\CashMovementReport;
use Modules\Report\Reports\CategorizedProductsReport;
use Modules\Report\Reports\CostRevenueByOrderReport;
use Modules\Report\Reports\CostRevenueReportByProductReport;
use Modules\Report\Reports\DiscountsAndVouchersReport;
use Modules\Report\Reports\GiftCard\GiftCardBatchReport;
use Modules\Report\Reports\GiftCard\GiftCardBranchPerformanceReport;
use Modules\Report\Reports\GiftCard\GiftCardExpiryReport;
use Modules\Report\Reports\GiftCard\GiftCardLiabilityReport;
use Modules\Report\Reports\GiftCard\GiftCardOutstandingBalanceReport;
use Modules\Report\Reports\GiftCard\GiftCardRedemptionReport;
use Modules\Report\Reports\GiftCard\GiftCardSalesReport;
use Modules\Report\Reports\GiftCard\GiftCardTransactionsReport;
use Modules\Report\Reports\IngredientUsageReport;
use Modules\Report\Reports\LowStockAlertsReport;
use Modules\Report\Reports\Loyalty\ActivePromotionsReport;
use Modules\Report\Reports\Loyalty\AvailableGiftsReport;
use Modules\Report\Reports\Loyalty\AverageOrderValueLoyaltyCustomersReport;
use Modules\Report\Reports\Loyalty\AveragePointsPerProgramReport;
use Modules\Report\Reports\Loyalty\AveragePointsPerRedemptionReport;
use Modules\Report\Reports\Loyalty\BonusVsMultiplierComparisonReport;
use Modules\Report\Reports\Loyalty\CategoryBoostPromotionsReport;
use Modules\Report\Reports\Loyalty\ExpiredGiftsReport;
use Modules\Report\Reports\Loyalty\ExpiredPromotionsReport;
use Modules\Report\Reports\Loyalty\FreeItemsCostReport;
use Modules\Report\Reports\Loyalty\GiftUsageRateReport;
use Modules\Report\Reports\Loyalty\HighestImpactPromotionsReport;
use Modules\Report\Reports\Loyalty\InactiveLoyaltyCustomersReport;
use Modules\Report\Reports\Loyalty\LeastUsedRewardsReport;
use Modules\Report\Reports\Loyalty\LoyaltyLastActivityReport;
use Modules\Report\Reports\Loyalty\LoyaltyProgramSummaryReport;
use Modules\Report\Reports\Loyalty\MostRedeemedRewardsReport;
use Modules\Report\Reports\Loyalty\NeverRedeemedRewardsReport;
use Modules\Report\Reports\Loyalty\NewMemberPromotionsReport;
use Modules\Report\Reports\Loyalty\NoRedemptionsReport;
use Modules\Report\Reports\Loyalty\PointsLifecycleTimelineReport;
use Modules\Report\Reports\Loyalty\PromotionUsageReport;
use Modules\Report\Reports\Loyalty\RedemptionRateReport;
use Modules\Report\Reports\Loyalty\RedemptionsByProgramReport;
use Modules\Report\Reports\Loyalty\RedemptionsByStatusReport;
use Modules\Report\Reports\Loyalty\RevenueBeforeAfterLoyaltyReport;
use Modules\Report\Reports\Loyalty\RevenueFromLoyaltyCustomersReport;
use Modules\Report\Reports\Loyalty\RewardsByProgramReport;
use Modules\Report\Reports\Loyalty\RewardsByTierReport;
use Modules\Report\Reports\Loyalty\RewardsByTypeReport;
use Modules\Report\Reports\Loyalty\SystemPointsBalanceReport;
use Modules\Report\Reports\Loyalty\TierCustomerDistributionReport;
use Modules\Report\Reports\Loyalty\TierRedemptionRateReport;
use Modules\Report\Reports\Loyalty\TopCustomersByPointsReport;
use Modules\Report\Reports\Loyalty\TotalEarnedPointsReport;
use Modules\Report\Reports\Loyalty\TotalExpiredPointsReport;
use Modules\Report\Reports\Loyalty\TotalRedeemedPointsReport;
use Modules\Report\Reports\Loyalty\UnusedGiftsPerCustomerReport;
use Modules\Report\Reports\Loyalty\UsedGiftsReport;
use Modules\Report\Reports\PaymentsReport;
use Modules\Report\Reports\ProductsPurchaseReport;
use Modules\Report\Reports\ProductTaxReport;
use Modules\Report\Reports\RegisterSummaryReport;
use Modules\Report\Reports\SalesByCashierReport;
use Modules\Report\Reports\SalesByCreatorReport;
use Modules\Report\Reports\SalesReport;
use Modules\Report\Reports\TaxReport;
use Modules\Report\Reports\UpcomingOrdersReport;

class ReportManager
{
    /**
     * instance of ReportsManagement
     *
     * @var ReportManager
     */
    private static ReportManager $instance;

    /**
     * Registered reports
     *
     * @var array
     */
    protected array $registeredReports = [];

    /**
     * Array of available reports.
     *
     * @var array
     */
    private array $reports = [
        SalesReport::class,
        ProductsPurchaseReport::class,
        TaxReport::class,
        ProductTaxReport::class,
        BranchPerformanceReport::class,
        PaymentsReport::class,
        DiscountsAndVouchersReport::class,
        GiftCardSalesReport::class,
        GiftCardRedemptionReport::class,
        GiftCardOutstandingBalanceReport::class,
        GiftCardLiabilityReport::class,
        GiftCardExpiryReport::class,
        GiftCardTransactionsReport::class,
        GiftCardBranchPerformanceReport::class,
        GiftCardBatchReport::class,
        IngredientUsageReport::class,
        LowStockAlertsReport::class,
        RegisterSummaryReport::class,
        CashMovementReport::class,
        SalesByCreatorReport::class,
        SalesByCashierReport::class,
        CategorizedProductsReport::class,
        UpcomingOrdersReport::class,
        CostRevenueByOrderReport::class,
        CostRevenueReportByProductReport::class,
        LoyaltyProgramSummaryReport::class,
        TotalEarnedPointsReport::class,
        TotalRedeemedPointsReport::class,
        TotalExpiredPointsReport::class,
        SystemPointsBalanceReport::class,
        RedemptionRateReport::class,
        AveragePointsPerProgramReport::class,
        PointsLifecycleTimelineReport::class,
        LoyaltyLastActivityReport::class,
        InactiveLoyaltyCustomersReport::class,
        NoRedemptionsReport::class,
        TopCustomersByPointsReport::class,
        TierCustomerDistributionReport::class,
        TierRedemptionRateReport::class,
        RevenueFromLoyaltyCustomersReport::class,
        RevenueBeforeAfterLoyaltyReport::class,
        AverageOrderValueLoyaltyCustomersReport::class,
        FreeItemsCostReport::class,
        MostRedeemedRewardsReport::class,
        LeastUsedRewardsReport::class,
        NeverRedeemedRewardsReport::class,
        RewardsByTypeReport::class,
        RewardsByTierReport::class,
        RewardsByProgramReport::class,
        AvailableGiftsReport::class,
        UsedGiftsReport::class,
        ExpiredGiftsReport::class,
        GiftUsageRateReport::class,
        UnusedGiftsPerCustomerReport::class,
        RedemptionsByStatusReport::class,
        RedemptionsByProgramReport::class,
        AveragePointsPerRedemptionReport::class,
        ActivePromotionsReport::class,
        ExpiredPromotionsReport::class,
        PromotionUsageReport::class,
        HighestImpactPromotionsReport::class,
        BonusVsMultiplierComparisonReport::class,
        CategoryBoostPromotionsReport::class,
        NewMemberPromotionsReport::class,
    ];

    /**
     * Get instance from ReportManager
     *
     * @return ReportManager
     */
    public static function getInstance(): ReportManager
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
            static::$instance->register(true);
        }

        return static::$instance;
    }

    /**
     * Register groups
     *
     * @param bool $force ;
     * @return void
     */
    public function register(bool $force = false): void
    {
        if (count($this->registeredReports) == 0 || $force) {
            foreach ($this->reports as $report) {
                $object = new $report;
                $this->registeredReports[$object->key()] = $object;
            }
        }
    }

    /**
     * Determine if group exists
     *
     * @param string $key
     * @return bool
     */
    public function reportExists(string $key): bool
    {
        return array_key_exists($key, $this->registeredReports());
    }

    /**
     * Register single report
     *
     * @param string|null $key
     * @return Report|array
     */
    public function registeredReports(?string $key = null): array|Report
    {
        return is_null($key) ? $this->registeredReports : $this->registeredReports[$key];
    }
}
