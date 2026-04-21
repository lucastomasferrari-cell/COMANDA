<script lang="ts" setup>
  import type { IconValue } from 'vuetify/lib/composables/icons.js'
  import Chart, { type ChartType } from 'chart.js/auto'
  import { nextTick, onMounted, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { http } from '@/modules/core/api/http.ts'

  const props = defineProps<{
    filters: Record<string, any>
    title: string
    endpoint: string
    type: ChartType
    icon: IconValue
    displayLegend?: boolean
    plugins?: (body: any) => any
  }>()

  const { t } = useI18n()
  const chartCanvas = ref<HTMLCanvasElement | null>(null)
  let chartInstance: any = null

  const loading = ref(true)
  const empty = ref(false)

  const fetchData = async () => {
    loading.value = true
    empty.value = false

    try {
      const response = await http.get(props.endpoint, {
        params: { ...props.filters },
      })

      const labels = response.data.body.labels || []
      const datasets = response.data.body.datasets || []

      await nextTick()

      if (!chartCanvas.value) return

      // Full cleanup before rendering new chart
      if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
      }

      const ctx = chartCanvas.value.getContext('2d')
      ctx?.clearRect(0, 0, chartCanvas.value.width, chartCanvas.value.height)

      if (labels.length === 0 || datasets.length === 0 || !datasets[0]?.data?.length) {
        empty.value = true
        return
      }

      chartInstance = new Chart(chartCanvas.value, {
        type: props.type,
        data: { labels, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: props.displayLegend || false,
            },
            ...(props.plugins ? props.plugins(response.data.body) : {}),
          },
        },
      })
    } catch {
      empty.value = true
    } finally {
      loading.value = false
    }
  }

  onMounted(fetchData)

  watch(
    () => props.filters,
    () => {
      fetchData()
    },
    { deep: true },
  )
</script>

<template>
  <VCard style="height: 400px;">
    <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-1 font-weight-bold text-h6">
      <VIcon color="primary" :icon="icon" size="20" />
      {{ title }}
    </VCardTitle>
    <VCardText
      v-if="loading||empty"
      class="d-flex justify-center align-center"
      style="height: calc(100% - 40px); position: relative;"
    >
      <VProgressCircular
        v-if="loading"
        color="primary"
        indeterminate
        size="40"
      />
      <div v-else-if="empty" class="text-medium-emphasis">
        {{ t('inventory::inventories.analytics.no_data_available') }}
      </div>
    </VCardText>
    <VCardText
      v-show="!loading && !empty"
      class="d-flex justify-center align-center"
      style="height: calc(100% - 40px); position: relative;"
    >
      <canvas
        ref="chartCanvas"
        style="height: 100%; width: 100%;"
      />
    </VCardText>
  </VCard>
</template>
