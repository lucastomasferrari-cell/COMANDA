<script lang="ts" setup>
  import Chart, { type ChartType, type TooltipItem } from 'chart.js/auto'
  import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'
  import { formatPrice } from '@/modules/core/utils/formatters.ts'

  const props = defineProps<{
    title: string
    icon: string
    section: string
    chartType: ChartType
    filters: Record<string, any>
    fetcher: (section: string, filters: Record<string, any>) => Promise<any>
    tone?: 'coral' | 'teal' | 'amber' | 'navy' | 'rose'
  }>()

  const chartCanvas = ref<HTMLCanvasElement | null>(null)
  const loading = ref(false)
  const empty = ref(false)
  const payload = ref<Record<string, any>>({})
  const { t } = useI18n()
  const appStore = useAppStore()
  let chartInstance: Chart | null = null

  const tones = {
    coral: {
      accent: '#f26b5b',
      fill: 'rgba(242, 107, 91, 0.16)',
      palette: ['#f26b5b', '#f7a072', '#f6bd60', '#84dcc6', '#7cc6fe'],
    },
    teal: {
      accent: '#1ba39c',
      fill: 'rgba(27, 163, 156, 0.16)',
      palette: ['#1ba39c', '#58c9b9', '#7cc6fe', '#c9b6ff', '#f2b134'],
    },
    amber: {
      accent: '#f2b134',
      fill: 'rgba(242, 177, 52, 0.18)',
      palette: ['#f2b134', '#f6bd60', '#f7d794', '#84dcc6', '#1ba39c'],
    },
    navy: {
      accent: '#2e4057',
      fill: 'rgba(46, 64, 87, 0.14)',
      palette: ['#2e4057', '#4f6d8a', '#7cc6fe', '#a8e6cf', '#f2b134'],
    },
    rose: {
      accent: '#d84b6b',
      fill: 'rgba(216, 75, 107, 0.15)',
      palette: ['#d84b6b', '#f26b5b', '#f6bd60', '#7cc6fe', '#c9b6ff'],
    },
  }

  const tone = computed(() => tones[props.tone || 'coral'])
  const isUsageByDayChart = computed(() => props.section === 'usage_by_day_of_week' && props.chartType === 'bar')
  const labels = computed(() => payload.value.labels || [])
  const series = computed(() => payload.value.series || [])

  const totalValue = computed(() => series.value.reduce((sum: number, value: number) => sum + Number(value || 0), 0))
  const peakPoint = computed(() => {
    if (series.value.length === 0) return null

    let peakIndex = 0
    let peakValue = Number(series.value[0] || 0)

    series.value.forEach((value: number, index: number) => {
      const numericValue = Number(value || 0)
      if (numericValue > peakValue) {
        peakValue = numericValue
        peakIndex = index
      }
    })

    return {
      label: labels.value[peakIndex] || '-',
      value: peakValue,
    }
  })

  const summaryItems = computed(() => {
    const items: Array<{ label: string, value: string }> = []
    const hasCurrency = Boolean(payload.value.currency)

    if (hasCurrency) {
      items.push({
        label: t('giftcard::gift_cards.analytics.total'),
        value: formatPrice(totalValue.value, payload.value.currency, payload.value.precision || 2),
      })
    } else {
      items.push({
        label: t('giftcard::gift_cards.analytics.total'),
        value: String(totalValue.value),
      })
    }

    items.push({
      label: t('giftcard::gift_cards.analytics.points'),
      value: String(labels.value.length),
    })

    if (peakPoint.value) {
      items.push({
        label: t('giftcard::gift_cards.analytics.peak'),
        value: hasCurrency
          ? `${peakPoint.value.label} · ${formatPrice(peakPoint.value.value, payload.value.currency, payload.value.precision || 2)}`
          : `${peakPoint.value.label} · ${peakPoint.value.value}`,
      })
    }

    return items
  })

  function destroyChart () {
    if (chartInstance) {
      chartInstance.destroy()
      chartInstance = null
    }
  }

  const hasSeries = (series: unknown) => Array.isArray(series) && series.some(value => Number(value) > 0)

  async function renderChart () {
    await nextTick()

    const ctx = chartCanvas.value?.getContext('2d')
    if (!ctx) return

    destroyChart()

    let labels = Array.isArray(payload.value.labels) ? [...payload.value.labels] : []
    let series = Array.isArray(payload.value.series) ? [...payload.value.series] : []

    if (isUsageByDayChart.value && appStore.isRtl) {
      labels = labels.reverse()
      series = series.reverse()
    }

    empty.value = labels.length === 0 || !hasSeries(series)
    if (empty.value) return

    const gradient = ctx.createLinearGradient(0, 0, 0, 260)
    gradient.addColorStop(0, tone.value.fill)
    gradient.addColorStop(1, 'rgba(255,255,255,0)')

    const usagePalette = [
      'rgba(216, 75, 107, 0.92)',
      'rgba(230, 102, 126, 0.88)',
      'rgba(242, 107, 91, 0.84)',
      'rgba(246, 189, 96, 0.82)',
      'rgba(124, 198, 254, 0.82)',
      'rgba(201, 182, 255, 0.84)',
      'rgba(216, 75, 107, 0.76)',
    ]
    const usageHoverPalette = [
      '#d84b6b',
      '#e6667e',
      '#f26b5b',
      '#f6bd60',
      '#7cc6fe',
      '#c9b6ff',
      '#c63f60',
    ]

    chartInstance = new Chart(ctx, {
      type: props.chartType,
      data: {
        labels,
        datasets: [
          {
            label: props.title,
            data: series,
            borderColor: tone.value.accent,
            backgroundColor: props.chartType === 'doughnut'
              ? tone.value.palette
              : (isUsageByDayChart.value
                ? labels.map((_: unknown, index: number) => usagePalette[index % usagePalette.length])
                : gradient),
            borderWidth: props.chartType === 'doughnut' ? 0 : (isUsageByDayChart.value ? 0 : 3),
            fill: props.chartType === 'line',
            tension: props.chartType === 'line' ? 0.34 : 0,
            borderRadius: props.chartType === 'bar' ? (isUsageByDayChart.value ? 18 : 8) : 0,
            hoverBackgroundColor: isUsageByDayChart.value ? usageHoverPalette : tone.value.palette,
            pointRadius: props.chartType === 'line' ? 0 : 3,
            pointHoverRadius: props.chartType === 'line' ? 5 : 4,
            maxBarThickness: props.chartType === 'bar' ? (isUsageByDayChart.value ? 52 : 38) : undefined,
            minBarLength: isUsageByDayChart.value ? 6 : undefined,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: props.chartType === 'doughnut',
            position: 'bottom',
            labels: {
              boxWidth: 10,
              boxHeight: 10,
              useBorderRadius: true,
              color: '#506070',
              padding: 14,
            },
          },
          tooltip: {
            displayColors: props.chartType === 'doughnut',
            callbacks: {
              label: (context: TooltipItem<ChartType>) => {
                const raw = Number(context.raw || 0)
                if (payload.value.currency) {
                  return formatPrice(raw, payload.value.currency, payload.value.precision || 2)
                }

                return String(raw)
              },
            },
          },
        },
        scales: props.chartType === 'doughnut'
          ? {}
          : {
            x: {
              grid: { display: false },
              ticks: { color: '#617082', font: { weight: isUsageByDayChart.value ? 700 : 400 } },
              border: { display: false },
            },
            y: {
              beginAtZero: true,
              ticks: { color: '#617082' },
              grid: {
                color: isUsageByDayChart.value ? 'rgba(80, 96, 112, 0.06)' : 'rgba(80, 96, 112, 0.08)',
                borderDash: isUsageByDayChart.value ? [4, 4] : undefined,
              },
              border: { display: false },
            },
          },
        cutout: props.chartType === 'doughnut' ? '68%' : undefined,
      } as any,
    })
  }

  async function load () {
    loading.value = true
    empty.value = false

    try {
      const response = await props.fetcher(props.section, props.filters || {})
      payload.value = response.data.body || {}
      loading.value = false
      await renderChart()
      return
    } catch {
      empty.value = true
    } finally {
      loading.value = false
    }
  }

  watch(() => props.filters, load, { deep: true })
  onBeforeUnmount(destroyChart)
</script>

<template>
  <VCard class="analytics-card">
    <VCardTitle class="analytics-card__title">
      <VIcon class="analytics-card__title-icon" :icon="icon" size="18" />
      <span>{{ title }}</span>
    </VCardTitle>
    <VCardText>
      <div v-if="!loading && !empty" class="summary-strip mt-4">
        <div v-for="item in summaryItems" :key="item.label" class="summary-pill">
          <span>{{ item.label }}</span>
          <strong>{{ item.value }}</strong>
        </div>
      </div>

      <div v-if="loading" class="chart-state">
        <VProgressCircular :color="tone.accent" indeterminate size="34" width="3" />
      </div>
      <div v-else-if="empty" class="chart-state text-medium-emphasis">
        {{ $t('admin::admin.table.no_data_available') }}
      </div>
      <div v-else class="chart-wrap">
        <canvas ref="chartCanvas" />
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

.summary-strip {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.summary-pill {
  display: grid;
  gap: 4px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px dashed rgba(var(--v-theme-on-surface), .12);
  background: rgba(var(--v-theme-surface), .85);
}

.summary-pill span {
  font-size: 12px;
  color: rgba(var(--v-theme-on-surface), .65);
}

.summary-pill strong {
  font-size: 18px;
}

.chart-wrap {
  position: relative;
  height: 350px;
  margin-top: 16px;
}

.chart-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 350px;
}

@media (max-width: 700px) {
  .summary-strip {
    grid-template-columns: 1fr;
  }
}
</style>
