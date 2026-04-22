<script lang="ts" setup>
  import { onBeforeUnmount, onMounted, ref } from 'vue'
  import { pulse } from '@/modules/admin/api/dashboard.api.ts'

  interface PulseData {
    sales_today: string
    orders_today: number
    orders_active: number
    currency: string
  }

  const data = ref<PulseData | null>(null)
  const loading = ref(false)
  let timer: number | null = null

  async function fetchPulse () {
    if (loading.value) return
    loading.value = true
    try {
      const response = await pulse()
      data.value = response.data.body
    } catch {
      // silencio: el pulse es best-effort, no interrumpe al usuario si falla
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    fetchPulse()
    timer = window.setInterval(fetchPulse, 60_000)
  })

  onBeforeUnmount(() => {
    if (timer !== null) window.clearInterval(timer)
  })
</script>

<template>
  <div
    v-if="data"
    class="navbar-pulse d-flex align-center gap-3 px-3 py-1 rounded"
    :title="$t('dashboard::dashboards.pulse_tooltip') ?? 'Pulse del día'"
  >
    <div class="pulse-item">
      <div class="pulse-label">{{ $t('dashboard::dashboards.sales_today') ?? 'Ventas' }}</div>
      <div class="pulse-value">{{ data.sales_today }}</div>
    </div>
    <VDivider vertical />
    <div class="pulse-item">
      <div class="pulse-label">{{ $t('dashboard::dashboards.orders_today') ?? 'Pedidos' }}</div>
      <div class="pulse-value">{{ data.orders_today }}</div>
    </div>
    <VDivider vertical />
    <div class="pulse-item">
      <div class="pulse-label">{{ $t('dashboard::dashboards.orders_active') ?? 'Activos' }}</div>
      <div class="pulse-value">{{ data.orders_active }}</div>
    </div>
  </div>
</template>

<style scoped>
.navbar-pulse {
  background: rgba(var(--v-theme-on-surface), 0.04);
}
.pulse-item {
  display: flex;
  flex-direction: column;
  line-height: 1;
}
.pulse-label {
  font-size: 0.65rem;
  opacity: 0.65;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.pulse-value {
  font-size: 0.9rem;
  font-weight: 600;
  margin-top: 2px;
}
</style>
