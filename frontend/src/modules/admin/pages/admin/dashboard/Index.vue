<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
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
  import SalesByPaymentMethodToday from './Partials/SalesByPaymentMethodToday.vue'
  import TopSellingProducts from './Partials/TopSellingProducts.vue'

  const { can, hasPermission } = useAuth()
  const { t } = useI18n()
  const router = useRouter()

  // Sprint 3.A.bis — atajo al POS. cartId es un UUID efímero (convención
  // heredada del vendor en NavbarViewPos.vue): identifica la "sesión de
  // caja" en el frontend, no un carrito persistente.
  function openPos (): void {
    router.push({
      name: 'admin.pos.index',
      params: { cartId: crypto.randomUUID() },
    } as unknown as RouteLocationRaw)
  }

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
  <VRow v-if="can('admin.pos.index')" class="mb-2">
    <VCol cols="12">
      <VCard
        class="pos-launcher-card"
        color="primary"
        variant="flat"
        @click="openPos"
      >
        <div class="d-flex align-center pa-5 ga-4">
          <div class="pos-launcher-icon">
            <VIcon color="white" icon="tabler-cash-register" size="32" />
          </div>
          <div class="flex-grow-1">
            <div class="text-h6 text-white font-weight-bold">
              {{ t('pos::pos_viewer.pos_viewer') }}
            </div>
            <div class="text-body-2 text-white opacity-80">
              {{ t('dashboard::dashboards.open_pos_subtitle') }}
            </div>
          </div>
          <VIcon color="white" icon="tabler-arrow-right" size="24" />
        </div>
      </VCard>
    </VCol>
  </VRow>
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
    <VCol v-if="can('admin.payment_methods.index')" cols="12" md="4" sm="6">
      <SalesByPaymentMethodToday />
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>
.pos-launcher-card {
  cursor: pointer;
  transition: transform 120ms ease, box-shadow 120ms ease;

  &:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
  }
}

.pos-launcher-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
</style>
