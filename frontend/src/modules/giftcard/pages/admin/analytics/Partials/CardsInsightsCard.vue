<script lang="ts" setup>
  import Chart from 'chart.js/auto'
  import { nextTick, ref, watch } from 'vue'
  import { useGiftCardAnalytics } from '@/modules/giftcard/composables/giftCardAnalytics.ts'

  const props = defineProps<{
    filters: Record<string, any>
    filtersLoading?: boolean
  }>()

  const { getGiftCardAnalyticsSection } = useGiftCardAnalytics()

  const chartRef = ref<HTMLCanvasElement | null>(null)
  const loading = ref(false)
  const data = ref<Record<string, any>>({
    status_mix: [],
    scope_mix: [],
    top_balances: [],
  })
  let chart: Chart | null = null

  function render () {
    const ctx = chartRef.value?.getContext('2d')
    if (!ctx) return
    if (chart) chart.destroy()

    const statusRows = data.value.status_mix || []
    const scopeRows = data.value.scope_mix || []
    const labels = [...new Set([
      ...statusRows.map((item: Record<string, any>) => item.label),
      ...scopeRows.map((item: Record<string, any>) => item.label),
    ])]

    chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: 'Status',
            data: labels.map(label => Number(statusRows.find((item: Record<string, any>) => item.label === label)?.count || 0)),
            backgroundColor: '#5B8FF9',
            borderRadius: 8,
          },
          {
            label: 'Scope',
            data: labels.map(label => Number(scopeRows.find((item: Record<string, any>) => item.label === label)?.count || 0)),
            backgroundColor: '#61DDAA',
            borderRadius: 8,
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
        },
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      } as any,
    })
  }

  async function load () {
    loading.value = true

    try {
      const response = await getGiftCardAnalyticsSection('cards', props.filters || {})
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
      <VIcon class="analytics-card__title-icon" icon="tabler-cards" size="18" />
      <span>{{ $t('giftcard::gift_cards.analytics.cards_health') }}</span>
    </VCardTitle>
    <VCardText>

      <div
        v-if="loading || filtersLoading"
        class="d-flex justify-center align-center chart-wrap short"
      >
        <VProgressCircular color="primary" indeterminate size="35" width="3" />
      </div>
      <div
        v-else-if="(data.status_mix || []).length === 0 && (data.scope_mix || []).length === 0"
        class="d-flex justify-center align-center chart-wrap short text-medium-emphasis"
      >
        {{ $t('dashboard::dashboards.no_data_available') }}
      </div>
      <div v-else class="chart-wrap short">
        <canvas ref="chartRef" />
      </div>

      <div class="list-title mt-4">{{ $t('giftcard::gift_cards.analytics.top_balances') }}</div>
      <div class="balances-list">
        <div v-for="item in data.top_balances" :key="item.id" class="balance-item">
          <div class="balance-main">
            <VAvatar color="primary" rounded size="40" variant="tonal">
              <span class="font-weight-bold">
                {{ String(item.code || '?').charAt(0) }}
              </span>
            </VAvatar>

            <div class="balance-copy">
              <div class="balance-title">{{ item.code }}</div>
              <div class="balance-meta">
                {{ item.customer || '-' }}
                <span class="balance-dot">•</span>
                {{ item.branch || $t('giftcard::gift_cards.analytics.global_pool') }}
              </div>
            </div>
          </div>

          <div class="balance-side">
            <div class="balance-value">{{ item.current_balance?.formatted || '-' }}</div>
            <div class="balance-label">{{ $t('giftcard::gift_cards.table.current_balance') }}</div>
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

.list-title {
  margin-bottom: 10px;
  font-size: 13px;
  font-weight: 700;
}

.chart-wrap.short {
  height: 300px;
}

.balances-list {
  max-height: 240px;
  overflow-y: auto;
  display: grid;
  gap: 10px;
}

.balance-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px dashed rgba(var(--v-theme-on-surface), .12);
  background: rgba(var(--v-theme-surface), .85);
}

.balance-main {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.balance-copy {
  min-width: 0;
}

.balance-title {
  font-size: 14px;
  font-weight: 700;
}

.balance-meta,
.balance-label {
  color: rgba(var(--v-theme-on-surface), .62);
  font-size: 12px;
}

.balance-dot {
  margin-inline: 4px;
}

.balance-side {
  text-align: end;
}

.balance-value {
  font-size: 15px;
  font-weight: 700;
}

</style>
