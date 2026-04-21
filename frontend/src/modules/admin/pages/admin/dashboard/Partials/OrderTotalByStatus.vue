<script lang="ts" setup>
  import Chart from 'chart.js/auto'
  import { ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useDashboard } from '@/modules/admin/composables/dashboard.ts'
  import { convertHexToRgba } from '@/modules/core/utils/color.ts'

  defineProps<{
    filters: Record<string, any>[]
  }>()

  const { t } = useI18n()
  const { orderTotalByStatus } = useDashboard()

  const loading = ref(false)
  const filter = ref('all_time')
  const chartRef = ref<HTMLCanvasElement | null>(null)
  let chartInstance: Chart | null = null
  const data = ref({ labels: [], data: [], colors: [] })
  const palette = ['#1d4ed8', '#f57c00', '#0f766e', '#e11d48', '#7c3aed', '#d97706']

  function renderChart () {
    const ctx = chartRef.value?.getContext('2d')
    if (!ctx) return

    if (chartInstance) chartInstance.destroy()

    chartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: data.value.labels,
        datasets: [{
          data: data.value.data,
          backgroundColor: (data.value.colors.length > 0 ? data.value.colors : palette).map(color => convertHexToRgba(color, 0.82)),
          borderColor: '#ffffff',
          borderWidth: 3,
          hoverOffset: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#444',
              padding: 20,
              boxWidth: 12,
              usePointStyle: true,
            },
          },
          tooltip: {
            displayColors: false,
            callbacks: {
              label: (ctx: any) => `${t('dashboard::dashboards.total_orders')}: ${ctx.raw}`,
            },
          },
        },
        cutout: '66%',
      } as any,
    })
  }

  watch(filter, () => loadData())

  onBeforeMount(() => loadData())
  onBeforeUnmount(() => {
    chartInstance?.destroy()
    chartInstance = null
  })

  async function loadData () {
    try {
      loading.value = true
      const response = await orderTotalByStatus(filter.value)
      data.value = response.data.body
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
        <VIcon class="dashboard-panel__icon" icon="tabler-clipboard-data" size="22" />
        {{ t('dashboard::dashboards.order_total_by_status') }}
      </div>
      <VSelect
        v-model="filter"
        class="dashboard-panel__select"
        density="compact"
        :disabled="loading"
        item-title="name"
        item-value="id"
        :items="filters"
        style="max-width: 140px"
        variant="outlined"
      />
    </VCardTitle>
    <VCardText>
      <div v-if="loading || data.data.length === 0" class="dashboard-panel__state">
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
      <div v-show="!loading && data.data.length > 0" class="dashboard-panel__chart">
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
  color: #1d4ed8;
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
