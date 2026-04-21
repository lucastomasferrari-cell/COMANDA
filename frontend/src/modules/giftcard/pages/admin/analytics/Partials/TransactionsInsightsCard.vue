<script lang="ts" setup>
  import Chart from 'chart.js/auto'
  import { computed, nextTick, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import { formatPrice } from '@/modules/core/utils/formatters.ts'
  import { useGiftCardAnalytics } from '@/modules/giftcard/composables/giftCardAnalytics.ts'

  const props = defineProps<{
    filters: Record<string, any>
    filtersLoading?: boolean
  }>()

  const { getGiftCardAnalyticsSection } = useGiftCardAnalytics()
  const { t } = useI18n()

  const chartRef = ref<HTMLCanvasElement | null>(null)
  const loading = ref(false)
  type TransactionBreakdownItem = {
    key: string
    label?: string
    count?: number
    amount?: {
      amount?: number
      formatted?: string
      currency?: string
      precision?: number
    }
  }

  const data = ref<{
    type_breakdown: TransactionBreakdownItem[]
    daily_trend: Record<string, any>[]
    recent_transactions: Record<string, any>[]
  }>({
    type_breakdown: [],
    daily_trend: [],
    recent_transactions: [],
  })
  let chart: Chart | null = null

  const highlightedTypes = computed(() => {
    const map = new Map<string, TransactionBreakdownItem>((data.value.type_breakdown || []).map(item => [item.key, item]))

    return ['purchase', 'redeem', 'expire', 'recharge'].map(key => ({
      key,
      label: map.get(key)?.label || t(`giftcard::gift_cards.analytics.${key}`),
      count: map.get(key)?.count ?? 0,
      amount: map.get(key)?.amount?.formatted ?? '-',
      amountRaw: Number(map.get(key)?.amount?.amount ?? 0),
      currency: map.get(key)?.amount?.currency || data.value.type_breakdown?.[0]?.amount?.currency || '',
      precision: Number(map.get(key)?.amount?.precision ?? data.value.type_breakdown?.[0]?.amount?.precision ?? 2),
    }))
  })

  function render () {
    const ctx = chartRef.value?.getContext('2d')
    if (!ctx) return
    if (chart) chart.destroy()

    const rows = highlightedTypes.value

    chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: rows.map(item => item.label),
        datasets: [
          {
            type: 'bar',
            label: t('giftcard::gift_cards.analytics.transactions'),
            data: rows.map(item => item.count),
            backgroundColor: '#5B8FF9',
            borderRadius: 8,
            yAxisID: 'y',
          },
          {
            type: 'line',
            label: t('giftcard::gift_cards.analytics.amount'),
            data: rows.map(item => item.amountRaw),
            borderColor: '#F59E0B',
            backgroundColor: 'rgba(245, 158, 11, .14)',
            pointBackgroundColor: '#F59E0B',
            tension: 0.35,
            yAxisID: 'y1',
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8 },
          },
          tooltip: {
            callbacks: {
              label: (context: any) => {
                if (context.datasetIndex === 0) {
                  return `${t('giftcard::gift_cards.analytics.transactions')}: ${context.raw}`
                }

                const first = rows[0]
                return `${t('giftcard::gift_cards.analytics.amount')}: ${formatPrice(context.raw as number, first?.currency || 'USD', first?.precision || 2)}`
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
          },
          y1: {
            beginAtZero: true,
            position: 'right',
            grid: { drawOnChartArea: false },
          },
        },
      } as any,
    })
  }

  async function load () {
    loading.value = true

    try {
      const response = await getGiftCardAnalyticsSection('transactions', props.filters || {})
      data.value = response.data.body || {}
    } finally {
      loading.value = false
    }

    await nextTick()
    render()
  }

  watch(() => props.filters, load, { deep: true })
</script>

<template>
  <VCard class="analytics-card">
    <VCardTitle class="analytics-card__title">
      <VIcon class="analytics-card__title-icon" icon="tabler-arrows-exchange" size="18" />
      <span>{{ $t('giftcard::gift_cards.analytics.transaction_mix') }}</span>
    </VCardTitle>
    <VCardText>

      <div
        v-if="loading || filtersLoading"
        class="d-flex justify-center align-center chart-wrap short"
      >
        <VProgressCircular color="primary" indeterminate size="35" width="3" />
      </div>
      <div
        v-else-if="!highlightedTypes.some(item => item.count > 0 || item.amountRaw > 0)"
        class="d-flex justify-center align-center chart-wrap short text-medium-emphasis"
      >
        {{ $t('dashboard::dashboards.no_data_available') }}
      </div>
      <div v-else class="chart-wrap short">
        <canvas ref="chartRef" />
      </div>

      <div class="list-title mt-5">
        {{ $t('giftcard::gift_cards.analytics.recent_transactions') }}
      </div>
      <div class="recent-list">
        <div v-for="item in data.recent_transactions" :key="item.id" class="recent-item">
          <div>
            <div class="row-label">{{ item.code || item.uuid }}</div>
            <div class="row-meta">
              <TableEnum column="type_label" :item="item" />
              ·
              {{ item.branch || $t('giftcard::gift_cards.analytics.global_pool') }}
            </div>
          </div>
          <div class="text-end">
            <strong>{{ item.amount?.formatted || '-' }}</strong>
            <div class="row-meta">{{ item.transaction_at }}</div>
          </div>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.analytics-card {
  min-height: 320px;
  border-radius: 14px;
}

.analytics-card__title {
  display: flex;
  align-items: center;
  gap: 8px;
}

.analytics-card__title-icon {
  color: rgb(var(--v-theme-primary));
}

.recent-list {
  display: grid;
  max-height: 240px;
  overflow-y: auto;
}

.recent-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 14px;
  border-bottom: 1px dashed rgba(var(--v-theme-on-surface), .12);
}

.chart-wrap.short {
  height: 300px;
}

.list-title,
.row-label {
  font-size: 13px;
  font-weight: 700;
}

.row-meta {
  color: rgba(var(--v-theme-on-surface), .62);
  font-size: 12px;
}

</style>
