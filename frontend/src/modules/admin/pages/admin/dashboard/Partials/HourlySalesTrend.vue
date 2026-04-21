<script lang="ts" setup>
  import Chart, { type TooltipItem } from 'chart.js/auto'
  import { ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useDashboard } from '@/modules/admin/composables/dashboard.ts'
  import { formatPrice } from '@/modules/core/utils/formatters.ts'

  const { t } = useI18n()
  const { hourlySalesTrend } = useDashboard()
  const loading = ref(true)

  const chartRef = ref<HTMLCanvasElement | null>(null)
  let chartInstance: Chart | null = null

  let chartData = { labels: [], data: [], currency: 'JOD' }

  const hasData = computed(() => (chartData.data || []).some(value => value > 0))

  function renderChart () {
    const ctx = chartRef.value?.getContext('2d')
    if (!ctx) return

    if (chartInstance) chartInstance.destroy()
    const gradient = ctx.createLinearGradient(0, 0, 0, 280)
    gradient.addColorStop(0, 'rgba(15, 118, 110, 0.22)')
    gradient.addColorStop(1, 'rgba(15, 118, 110, 0.03)')

    chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: chartData.labels,
        datasets: [
          {
            label: t('dashboard::dashboards.sales'),
            data: chartData.data,
            borderColor: '#0f766e',
            backgroundColor: gradient,
            borderWidth: 3,
            fill: true,
            tension: 0.38,
            pointRadius: 2,
            pointHoverRadius: 5,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#0f766e',
            pointBorderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            displayColors: false,
            callbacks: {
              label: (context: TooltipItem<'line'>) => {
                return `${t('dashboard::dashboards.sales')}: ${formatPrice(context.raw as number, chartData.currency)}`
              },
            },
          },
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#64748b' },
            border: { display: false },
          },
          y: {
            beginAtZero: true,
            ticks: { color: '#64748b' },
            grid: { color: 'rgba(148, 163, 184, .14)' },
            border: { display: false },
          },
        },
      } as any,
    })
  }

  onBeforeMount(() => loadData())
  onBeforeUnmount(() => {
    chartInstance?.destroy()
    chartInstance = null
  })

  async function loadData () {
    try {
      loading.value = true
      const response = await hourlySalesTrend()
      chartData = response.data.body
      renderChart()
    } catch {} finally {
      loading.value = false
    }
  }
</script>
<template>
  <VCard class="dashboard-panel" height="380">
    <VCardTitle class="dashboard-panel__header">
      <div class="dashboard-panel__title">
        <VIcon class="dashboard-panel__icon" icon="tabler-hourglass-low" size="22" />
        {{ t('dashboard::dashboards.hourly_sales_trend') }}
      </div>
    </VCardTitle>
    <VCardText>
      <div v-if="loading || !hasData" class="dashboard-panel__state">
        <VProgressCircular
          v-if="loading"
          color="primary"
          indeterminate
          size="40"
        />
        <span v-else class="text-body-1">
          {{ t('dashboard::dashboards.no_data_available') }}
        </span>
      </div>
      <div v-show="!loading && hasData" class="dashboard-panel__chart">
        <canvas ref="chartRef" height="300" />
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.dashboard-panel {
  border: 1px solid rgba(var(--v-theme-on-surface), .08);
  border-radius: 18px;
  box-shadow: 0 18px 36px rgba(15, 23, 42, .05);
}

.dashboard-panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding-block-end: 14px;
  margin-block-end: 8px;
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), .08);
  font-size: 1.1rem;
  font-weight: 800;
}

.dashboard-panel__title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.dashboard-panel__icon {
  color: #0f766e;
}

.dashboard-panel__state,
.dashboard-panel__chart {
  height: 300px;
}

.dashboard-panel__state {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
