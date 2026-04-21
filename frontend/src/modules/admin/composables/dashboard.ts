import {
  bestPerformingBranches,
  branchWiseSalesComparison,
  cashMovementsOverview,
  hourlySalesTrend,
  lowStockAlerts,
  orderTotalByStatus,
  orderTypeDistribution,
  overview,
  paymentsOverview,
  salesAnalytics,
  topSellingProducts,
} from '@/modules/admin/api/dashboard.api.ts'

export function useDashboard () {
  return {
    overview,
    salesAnalytics,
    bestPerformingBranches,
    orderTypeDistribution,
    orderTotalByStatus,
    paymentsOverview,
    hourlySalesTrend,
    branchWiseSalesComparison,
    cashMovementsOverview,
    topSellingProducts,
    lowStockAlerts,
  }
}
