<script lang="ts" setup>
  import type { ChartType } from 'chart.js/auto'
  import type { IconValue } from 'vuetify/lib/composables/icons.js'
  import { onMounted, reactive, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { http } from '@/modules/core/api/http.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import ChartComponent from './Partials/ChartComponent.vue'
  import IngredientPurchases from './Partials/IngredientPurchases.vue'
  import LowStockIngredients from './Partials/LowStockIngredients.vue'

  interface ChartConfig {
    title?: string
    key: string
    endpoint?: string
    cols: number
    icon?: IconValue
    displayLegend?: boolean
    type?: ChartType
    plugins?: (body: any) => any
  }

  const { t } = useI18n()

  const meta = ref({ branches: [] })
  const { user } = useAuth()

  const filters = reactive({
    from: null,
    to: null,
    branch_id: null,
  })

  const charts: ChartConfig[] = [
    {
      title: t('inventory::inventories.analytics.fast_moving_ingredients'),
      key: 'fast-moving-ingredients',
      endpoint: '/v1/inventories/analytics/fast-moving-ingredients',
      type: 'line',
      cols: 12,
      icon: 'tabler-tools-kitchen-3',
      displayLegend: true,
    },
    {
      title: t('inventory::inventories.analytics.top_suppliers'),
      key: 'top-suppliers',
      endpoint: '/v1/inventories/analytics/top-suppliers',
      type: 'bar',
      cols: 8,
      icon: 'tabler-truck-loading',
      plugins: response => {
        return {
          tooltip: {
            callbacks: {
              label: (context: Record<string, any>) => {
                const value = context.formattedValue
                const currency = response.currency ?? ''
                return `${t('inventory::inventories.analytics.purchase_amount')}: ${currency} ${value}`
              },
            },
          },
        }
      },
    },
    { key: 'ingredient_purchases', cols: 4 },
    {
      title: t('inventory::inventories.analytics.most_wasted_ingredients'),
      key: 'most-wasted-ingredients',
      endpoint: '/v1/inventories/analytics/most-wasted-ingredients',
      type: 'bar',
      cols: 8,
      icon: 'tabler-trash',
      plugins: response => {
        return {
          tooltip: {
            callbacks: {
              label: (context: any) => {
                const value = context.formattedValue
                return `${t('inventory::inventories.analytics.quantity')}: ${value} ${response.unitSymbols?.[context.dataIndex] ?? ''}`
              },
            },
          },
        }
      },
    },
    { key: 'low_stock_ingredients', cols: 4 },
    {
      title: t('inventory::inventories.analytics.purchase_status'),
      key: 'purchase-status-summary',
      endpoint: '/v1/inventories/analytics/purchase-status-summary',
      type: 'doughnut',
      cols: 4,
      displayLegend: true,
      icon: 'tabler-report-money',
    },
    {
      title: t('inventory::inventories.analytics.stock_movements'),
      key: 'stock-movement-summary',
      endpoint: '/v1/inventories/analytics/stock-movement-summary',
      type: 'doughnut',
      cols: 4,
      icon: 'tabler-chart-histogram',
      displayLegend: true,
    },
    {
      title: t('inventory::inventories.analytics.wastage_and_spoilage'),
      key: 'wastage-and-spoilage',
      endpoint: '/v1/inventories/analytics/wastage-and-spoilage',
      type: 'doughnut',
      cols: 4,
      displayLegend: true,
      icon: 'tabler-trash-x',
    },
  ]

  onMounted(() => {
    fetchMetaData()
  })

  const fetchMetaData = async () => {
    if (!user?.assigned_to_branch) {
      try {
        const response = await http.get('/v1/inventories/analytics/meta/data')
        meta.value.branches = response.data.body.branches
      } catch {}
    }
  }
</script>

<template>
  <VCard class="mb-3">
    <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-1 font-weight-bold text-h6">
      <VIcon color="primary" icon="tabler-filter" size="20" />
      {{ t('inventory::inventories.analytics.filters') }}
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol v-if="!user?.assigned_to_branch" cols="12" md="3">
          <VSelect
            v-model="filters.branch_id"
            clearable
            item-title="name"
            item-value="id"
            :items="meta.branches"
            :label="t('inventory::inventories.analytics.branch')"
          />
        </VCol>
        <VCol cols="12" md="3">
          <DatePicker
            v-model="filters.from"
            clearable
            :label="t('inventory::inventories.analytics.from')"
            :max="new Date().toLocaleDateString('en-CA')"
          />
        </VCol>
        <VCol cols="12" md="3">
          <DatePicker
            v-model="filters.to"
            clearable
            :label="t('inventory::inventories.analytics.to')"
            :max="new Date().toLocaleDateString('en-CA')"
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
  <VRow>
    <VCol
      v-for="chart in charts"
      :key="chart.title"
      cols="12"
      :md="chart.cols||6"
    >
      <IngredientPurchases v-if="chart.key==='ingredient_purchases'" :filters="filters" />
      <LowStockIngredients v-else-if="chart.key==='low_stock_ingredients'" :filters="filters" />
      <ChartComponent
        v-else-if="chart.endpoint && chart.icon && chart.title && chart.type"
        :display-legend="chart.displayLegend"
        :endpoint="chart.endpoint"
        :filters="filters"
        :icon="chart.icon"
        :plugins="chart?.plugins"
        :title="chart.title"
        :type="chart.type"
      />
    </VCol>
  </VRow>
</template>
