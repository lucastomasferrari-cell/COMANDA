<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import BestPerformingBranches from './Partials/BestPerformingBranches.vue'
  import BranchWiseSalesComparison from './Partials/BranchWiseSalesComparison.vue'
  import CashMovementsOverview from './Partials/CashMovementsOverview.vue'
  import HourlySalesTrend from './Partials/HourlySalesTrend.vue'
  import LowStockAlerts from './Partials/LowStockAlerts.vue'
  import OrderTotalByStatus from './Partials/OrderTotalByStatus.vue'
  import OrderTypeDistribution from './Partials/OrderTypeDistribution.vue'
  import Overview from './Partials/Overview.vue'
  import PaymentsOverview from './Partials/PaymentsOverview.vue'
  import SalesAnalytics from './Partials/SalesAnalytics.vue'
  import TopSellingProducts from './Partials/TopSellingProducts.vue'

  const { can, hasPermission } = useAuth()
  const { t } = useI18n()

  const globalFilters = [
    { id: 'all_time', name: t('dashboard::dashboards.all_time') },
    { id: 'today', name: t('dashboard::dashboards.today') },
    { id: 'this_week', name: t('dashboard::dashboards.this_week') },
    { id: 'this_month', name: t('dashboard::dashboards.this_month') },
    { id: 'this_year', name: t('dashboard::dashboards.this_year') },
  ]

  function showWidget (widget: string) {
    switch (widget) {
      case 'overview': {
        return hasPermission([
          'admin.dashboards.total_sales',
          'admin.dashboards.total_orders',
          'admin.dashboards.total_active_orders',
          'admin.dashboards.average_order_value',
          'admin.dashboards.total_users',
          'admin.dashboards.total_menus',
          'admin.dashboards.total_products',
          'admin.dashboards.total_categories',
        ])
      }
      default: {
        return false
      }
    }
  }
</script>

<template>
  <Overview v-if="showWidget('overview')" />
  <VRow v-if="can('admin.dashboards.sales_analytics')">
    <VCol cols="12">
      <HourlySalesTrend />
    </VCol>
  </VRow>
  <VRow
    v-if="hasPermission(['admin.dashboards.sales_analytics','admin.dashboards.top_selling_products'])"
  >
    <VCol v-if="can('admin.dashboards.sales_analytics')" cols="12" md="8">
      <SalesAnalytics />
    </VCol>
    <VCol v-if="can('admin.dashboards.top_selling_products')" cols="12" md="4">
      <TopSellingProducts :filters="globalFilters" />
    </VCol>
  </VRow>
  <VRow
    v-if="hasPermission(['admin.dashboards.branch_wise_sales_comparison','admin.dashboards.best_performing_branches'])"
  >
    <VCol v-if="can('admin.dashboards.branch_wise_sales_comparison')" cols="12" md="8">
      <BranchWiseSalesComparison :filters="globalFilters" />
    </VCol>
    <VCol v-if="can('admin.dashboards.best_performing_branches')" cols="12" md="4">
      <BestPerformingBranches :filters="globalFilters" />
    </VCol>
  </VRow>
  <VRow
    v-if="hasPermission(['admin.dashboards.order_type_distribution','admin.dashboards.order_total_by_status','admin.dashboards.low_stock_alerts'])"
  >
    <VCol v-if="can('admin.dashboards.order_type_distribution')" cols="12" md="4" sm="6">
      <OrderTypeDistribution :filters="globalFilters" />
    </VCol>
    <VCol v-if="can('admin.dashboards.order_total_by_status')" cols="12" md="4" sm="6">
      <OrderTotalByStatus :filters="globalFilters" />
    </VCol>
    <VCol v-if="can('admin.dashboards.low_stock_alerts')" cols="12" md="4" sm="6">
      <LowStockAlerts />
    </VCol>
  </VRow>
  <VRow
    v-if="hasPermission(['admin.dashboards.cash_movements_overview','admin.dashboards.payments_overview'])"
  >
    <VCol v-if="can('admin.dashboards.cash_movements_overview')" cols="12" md="4" sm="6">
      <CashMovementsOverview :filters="globalFilters" />
    </VCol>
    <VCol v-if="can('admin.dashboards.payments_overview')" cols="12" md="4" sm="6">
      <PaymentsOverview :filters="globalFilters" />
    </VCol>
  </VRow>
</template>
