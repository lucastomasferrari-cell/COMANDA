<script lang="ts" setup>
  import { onBeforeUnmount, onMounted, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { usePaymentMethod } from '@/modules/payment/composables/paymentMethod.ts'

  const { t } = useI18n()
  const { getReport } = usePaymentMethod()

  const loading = ref(false)
  const rows = ref<Array<Record<string, any>>>([])
  const totalAmount = ref(0)
  // Sprint 3.A.bis post-validación 3 — guard contra mutaciones
  // post-unmount. Si el user navega fuera del dashboard mientras
  // load() está esperando la API, la response llega a un componente
  // ya desmontado → Vue crash __vnode null.
  let isAlive = true

  async function load () {
    if (!isAlive) return
    loading.value = true
    try {
      const today = new Date().toISOString().slice(0, 10)
      const res = await getReport(today, today)
      if (!isAlive) return
      rows.value = res.data.body ?? []
      totalAmount.value = rows.value.reduce((sum, r) => sum + Number(r.total_amount ?? 0), 0)
    } catch {
      if (!isAlive) return
      rows.value = []
    } finally {
      if (isAlive) loading.value = false
    }
  }

  onBeforeUnmount(() => { isAlive = false })

  function pct (amount: number): number {
    return totalAmount.value > 0 ? (amount / totalAmount.value) * 100 : 0
  }

  function fmt (amount: number): string {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 })
      .format(amount || 0)
  }

  onMounted(load)
</script>

<template>
  <VCard>
    <VCardTitle class="d-flex align-center">
      <VIcon class="me-2" color="primary" icon="tabler-credit-card-pay" size="20" />
      {{ t('payment::payment_methods.report.title') }}
      <VSpacer />
      <span class="text-caption text-medium-emphasis">{{ t('dashboard::dashboards.today') }}</span>
    </VCardTitle>
    <VCardText>
      <div v-if="loading" class="text-center py-6">
        <VProgressCircular color="primary" indeterminate size="28" />
      </div>
      <div v-else-if="rows.length === 0" class="text-center py-6 text-caption text-medium-emphasis">
        {{ t('payment::payment_methods.report.no_data') }}
      </div>
      <div v-else class="d-flex flex-column gap-2">
        <div
          v-for="row in rows"
          :key="row.method_id ?? row.method_type"
          class="d-flex align-center"
        >
          <div class="flex-grow-1">
            <div class="text-body-2 font-weight-medium">{{ row.method_name }}</div>
            <VProgressLinear
              class="mt-1"
              color="primary"
              height="4"
              :model-value="pct(row.total_amount)"
              rounded
            />
          </div>
          <div class="text-end ms-3" style="min-width: 110px;">
            <div class="text-body-2 font-weight-bold">{{ fmt(row.total_amount) }}</div>
            <div class="text-caption text-medium-emphasis">
              {{ row.transactions_count }} {{ row.transactions_count === 1 ? 'venta' : 'ventas' }}
            </div>
          </div>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>
