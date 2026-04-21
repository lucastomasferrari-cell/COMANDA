<script lang="ts" setup>
  import type { TableFilterSchema } from '@/modules/core/contracts/Table.ts'
  import { computed, onMounted, ref } from 'vue'
  import TableFilters from '@/modules/core/components/Table/TableFilters.vue'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'
  import { getGiftCardAnalyticsSection } from '@/modules/giftcard/api/giftCardAnalytics.api.ts'
  import { useGiftCardAnalytics } from '@/modules/giftcard/composables/giftCardAnalytics.ts'
  import BatchesInsightsCard
    from '@/modules/giftcard/pages/admin/analytics/Partials/BatchesInsightsCard.vue'
  import BranchHighlightsCard
    from '@/modules/giftcard/pages/admin/analytics/Partials/BranchHighlightsCard.vue'
  import CardsInsightsCard
    from '@/modules/giftcard/pages/admin/analytics/Partials/CardsInsightsCard.vue'
  import ChartCard from '@/modules/giftcard/pages/admin/analytics/Partials/ChartCard.vue'
  import ExpiryWatchCard from '@/modules/giftcard/pages/admin/analytics/Partials/ExpiryWatchCard.vue'
  import OverviewCards from '@/modules/giftcard/pages/admin/analytics/Partials/OverviewCards.vue'
  import TransactionsInsightsCard
    from '@/modules/giftcard/pages/admin/analytics/Partials/TransactionsInsightsCard.vue'

  const appStore = useAppStore()
  const { getGiftCardAnalyticsFilters } = useGiftCardAnalytics()

  const filters = ref<Record<string, any>>({})
  const filterSchema = ref<TableFilterSchema[]>([])
  const filtersLoading = ref(false)

  const filtersKey = computed(() => JSON.stringify(filterSchema.value.map(item => item.key)))
  const currency = computed(() => String(filters.value.currency || appStore.currency || 'JOD'))
  const currencyPrecision = computed(() => Number(filters.value.currency_precision ?? 2))

  async function loadFilters () {
    filtersLoading.value = true

    try {
      const response = await getGiftCardAnalyticsFilters(filters.value)
      const schema = response.data.body.filters || []
      const defaults = response.data.body.default_filters || {}

      filterSchema.value = schema.map((item: TableFilterSchema) => ({
        ...item,
        clearable: item.key === 'branch_id' ? false : item.clearable,
        default: defaults[item.key] ?? item.default ?? null,
      }))

      if (Object.keys(filters.value).length === 0) {
        filters.value = { ...defaults }
      }
    } finally {
      filtersLoading.value = false
    }
  }

  function applyFilters (values: Record<string, any>) {
    const nextValues = { ...values }
    const branchFilter = filterSchema.value.find(item => item.key === 'branch_id')
    const selectedBranch = (branchFilter?.options || []).find((item: any) => Number(item.id) === Number(nextValues.branch_id)) as Record<string, any> | undefined

    nextValues.currency = selectedBranch?.currency || nextValues.currency || appStore.currency
    nextValues.currency_precision = Number(selectedBranch?.currency_precision ?? nextValues.currency_precision ?? 2)

    filters.value = nextValues
  }

  onMounted(async () => {
    await loadFilters()
  })

</script>

<template>
  <div class="gift-card-analytics-page">
    <div class="header">
      <div class="hero-copy" />

      <TableFilters
        v-if="filterSchema.length > 0"
        :key="filtersKey"
        :filter-schema="filterSchema"
        :loading="filtersLoading"
        @apply="applyFilters"
        @reload="loadFilters"
      />
    </div>

    <VRow>
      <VCol cols="12">
        <OverviewCards
          :currency="currency"
          :filters="filters"
          :filters-loading="filtersLoading"
          :precision="currencyPrecision"
        />
      </VCol>

      <VCol cols="12" md="6">
        <CardsInsightsCard :filters="filters" :filters-loading="filtersLoading" />
      </VCol>
      <VCol cols="12" md="6">
        <TransactionsInsightsCard :filters="filters" :filters-loading="filtersLoading" />
      </VCol>

      <VCol cols="12" md="6">
        <BatchesInsightsCard :filters="filters" :filters-loading="filtersLoading" />
      </VCol>
      <VCol cols="12" md="6">
        <BranchHighlightsCard
          :currency="currency"
          :filters="filters"
          :filters-loading="filtersLoading"
          :precision="currencyPrecision"
        />
      </VCol>

      <VCol cols="12" md="6">
        <ChartCard
          chart-type="line"
          :fetcher="getGiftCardAnalyticsSection"
          :filters="filters"
          icon="tabler-chart-line"
          section="sales_over_time"
          :title="$t('giftcard::gift_cards.analytics.sales_over_time')"
          tone="coral"
        />
      </VCol>
      <VCol cols="12" md="6">
        <ChartCard
          chart-type="line"
          :fetcher="getGiftCardAnalyticsSection"
          :filters="filters"
          icon="tabler-chart-dots-3"
          section="redemption_over_time"
          :title="$t('giftcard::gift_cards.analytics.redemption_over_time')"
          tone="teal"
        />
      </VCol>

      <VCol cols="12">
        <ChartCard
          chart-type="bar"
          :fetcher="getGiftCardAnalyticsSection"
          :filters="filters"
          icon="tabler-calendar-week"
          section="usage_by_day_of_week"
          :title="$t('giftcard::gift_cards.analytics.usage_by_day_of_week')"
          tone="rose"
        />
      </VCol>

      <VCol cols="12">
        <ExpiryWatchCard :filters="filters" :filters-loading="filtersLoading" />
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss" scoped>
.gift-card-analytics-page {
  display: grid;
  gap: 14px;
}

.header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
}

.hero-copy {
  max-width: 560px;
}

.hero-kicker {
  color: #f26b5b;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
}

.hero-title {
  margin-top: 6px;
  font-size: 28px;
  font-weight: 800;
  line-height: 1.05;
}

.hero-text {
  margin-top: 8px;
  color: rgba(var(--v-theme-on-surface), .68);
  font-size: 14px;
  line-height: 1.6;
}

@media (max-width: 960px) {
  .header {
    flex-direction: column;
  }

  .hero-title {
    font-size: 24px;
  }
}
</style>
