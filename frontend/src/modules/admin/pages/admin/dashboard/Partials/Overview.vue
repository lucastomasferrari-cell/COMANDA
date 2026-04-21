<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useDashboard } from '@/modules/admin/composables/dashboard.ts'
  import KpiCard from '@/modules/admin/pages/admin/dashboard/Partials/KpiCard.vue'

  const { t } = useI18n()
  const { overview } = useDashboard()

  const data: Record<string, any> = ref({})
  const loading = ref(true)

  const kpiCards: Record<string, any>[] = [
    {
      key: 'total_sales',
      color: '#f57c00',
      icon: 'tabler-basket-dollar',
    },
    {
      key: 'total_orders',
      color: '#0f766e',
      icon: 'tabler-salad',
    },
    {
      key: 'total_active_orders',
      color: '#dc2626',
      icon: 'tabler-receipt-2',
    },
    {
      key: 'average_order_value',
      color: '#1d4ed8',
      icon: 'tabler-chart-donut-3',
    },
    {
      key: 'total_users',
      color: '#7c3aed',
      icon: 'tabler-users',
    },
    {
      key: 'total_menus',
      color: '#0891b2',
      icon: 'tabler-list-details',
    },
    {
      key: 'total_products',
      color: '#e11d48',
      icon: 'tabler-package',
    },
    {
      key: 'total_categories',
      color: '#d97706',
      icon: 'tabler-folders',
    },
  ]

  onBeforeMount(async () => {
    try {
      loading.value = true
      const response = await overview()
      data.value = response.data.body
    } catch {} finally {
      loading.value = false
    }
  })
</script>

<template>
  <VRow class="mt-1">
    <KpiCard
      v-for="kpi in kpiCards"
      :key="kpi.key"
      :color="kpi.color"
      :icon="kpi.icon"
      :loading="loading"
      :permission="`admin.dashboards.${kpi.key}`"
      :title="t(`dashboard::dashboards.overview.${kpi.key}` )"
      :value="data[kpi.key]"
    />
  </VRow>
</template>
