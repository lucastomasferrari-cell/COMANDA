<script lang="ts" setup>
  import type { AxiosError } from 'axios'
  import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
  import { useRouter } from 'vue-router'
  import { useToast } from 'vue-toastification'
  import { useOrder } from '@/modules/sale/composables/order.ts'

  const props = defineProps<{
    branchId: number | null
    cartId: string
  }>()

  const emit = defineEmits<{
    (e: 'init-order', response: Record<string, any>): void
  }>()

  const { activeOrders, edit } = useOrder()
  const router = useRouter()
  const toast = useToast()

  interface ActiveOrder {
    id: number
    reference_no: string
    order_number: string
    status: string
    payment_status: string
    type: string
    table: { id: number; name: string } | null
    customer: { id: number; name: string } | null
    total: { formatted: string } | null
    waiter: { name: string } | null
    created_at: string
  }

  const orders = ref<ActiveOrder[]>([])
  const loading = ref(false)
  const loadingOrderId = ref<number | null>(null)
  const currentTime = ref(Date.now())
  let pollingTimer: number | null = null
  let clockTimer: number | null = null

  async function fetchActiveOrders (showLoader = true) {
    if (props.branchId === null) return
    if (showLoader) loading.value = true
    try {
      const response = await activeOrders(props.branchId, {})
      orders.value = response.data.body.orders || []
    } catch {
      // silencio: polling no interrumpe al usuario. El empty-state cubre visual.
    } finally {
      loading.value = false
    }
  }

  /**
   * Color del punto segun order.status.
   * El "cuenta pedida" (rojo) no existe como estado en el backend actual;
   * se agregaria como bill_requested_at o similar en una iteracion futura.
   * "En pausa" (gris) tampoco — idem.
   */
  function statusColor (status: string): string {
    switch (status) {
      case 'pending':
        return '#2ecc71' // verde: en curso
      case 'confirmed':
      case 'preparing':
        return '#f1c40f' // amarillo: en cocina
      case 'ready':
        return '#e67e22' // naranja: listo para servir
      default:
        return '#95a5a6' // gris: otros estados residuales
    }
  }

  function statusLabel (status: string): string {
    return {
      pending: 'En curso',
      confirmed: 'En cocina',
      preparing: 'En cocina',
      ready: 'Listo',
    }[status] ?? status
  }

  function contextLabel (o: ActiveOrder): string {
    if (o.table?.name) return o.table.name
    if (o.type === 'takeaway') return 'Para llevar'
    if (o.type === 'drive_thru') return 'Drive-Thru'
    if (o.type === 'pre_order') return 'Pedido anticipado'
    if (o.type === 'catering') return 'Catering'
    return o.customer?.name ?? 'Sin mesa'
  }

  function elapsedLabel (iso: string): string {
    const diffMs = currentTime.value - new Date(iso).getTime()
    const minutes = Math.max(0, Math.floor(diffMs / 60000))
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    if (hours > 0) return `${hours}:${mins.toString().padStart(2, '0')}`
    return `0:${mins.toString().padStart(2, '0')}`
  }

  const totalCount = computed(() => orders.value.length)

  async function openOrder (order: ActiveOrder) {
    if (loadingOrderId.value !== null) return
    loadingOrderId.value = order.id
    try {
      const response = (await edit(props.cartId, order.id)).data.body
      emit('init-order', response)
    } catch (error) {
      toast.error((error as AxiosError<{ message?: string }>).response?.data?.message ?? 'No se pudo abrir la comanda.')
    } finally {
      loadingOrderId.value = null
    }
  }

  function newOrder () {
    router.push({
      name: 'admin.pos.index',
      params: { cartId: crypto.randomUUID() },
    } as any)
  }

  onMounted(() => {
    fetchActiveOrders(true)
    pollingTimer = window.setInterval(() => fetchActiveOrders(false), 30_000)
    clockTimer = window.setInterval(() => { currentTime.value = Date.now() }, 30_000)
  })

  onBeforeUnmount(() => {
    if (pollingTimer !== null) window.clearInterval(pollingTimer)
    if (clockTimer !== null) window.clearInterval(clockTimer)
  })
</script>

<template>
  <div class="active-orders-panel d-flex flex-column h-100">
    <div class="panel-header d-flex align-center px-3 py-2">
      <h3 class="text-subtitle-1 font-weight-medium flex-grow-1">
        Comandas activas
        <span v-if="totalCount > 0" class="text-medium-emphasis text-body-2">({{ totalCount }})</span>
      </h3>
      <VBtn
        color="primary"
        density="compact"
        prepend-icon="tabler-plus"
        size="small"
        variant="tonal"
        @click="newOrder"
      >
        Nueva
      </VBtn>
    </div>

    <div v-if="loading && orders.length === 0" class="flex-grow-1 d-flex align-center justify-center">
      <VProgressCircular color="primary" indeterminate size="30" />
    </div>

    <div
      v-else-if="orders.length === 0"
      class="flex-grow-1 d-flex flex-column align-center justify-center text-center px-4"
    >
      <VIcon class="mb-2" color="grey-500" icon="tabler-inbox" size="40" />
      <p class="text-body-2 text-medium-emphasis mb-3">
        No hay comandas activas.
      </p>
      <VBtn color="primary" prepend-icon="tabler-plus" @click="newOrder">
        Empezar una nueva
      </VBtn>
    </div>

    <div v-else class="orders-list flex-grow-1 px-2 py-2">
      <div
        v-for="order in orders"
        :key="order.id"
        class="order-card mb-2 pa-3"
        :class="{ loading: loadingOrderId === order.id }"
        @click="openOrder(order)"
      >
        <div class="d-flex align-start mb-1">
          <span class="status-dot me-2" :style="{ background: statusColor(order.status) }" />
          <div class="flex-grow-1">
            <div class="d-flex align-center gap-2">
              <span class="order-number text-body-2 font-weight-bold">#{{ order.order_number }}</span>
              <span class="text-caption text-medium-emphasis">{{ statusLabel(order.status) }}</span>
            </div>
            <div class="context-label text-body-2 font-weight-medium mt-1">
              {{ contextLabel(order) }}
            </div>
          </div>
          <span class="text-caption text-medium-emphasis">{{ elapsedLabel(order.created_at) }}</span>
        </div>
        <div class="d-flex align-center mt-2">
          <span class="text-body-2 font-weight-bold">{{ order.total?.formatted ?? '—' }}</span>
          <VSpacer />
          <span v-if="order.waiter?.name" class="text-caption text-medium-emphasis">
            {{ order.waiter.name }}
          </span>
        </div>
      </div>
    </div>

    <div class="panel-footer px-3 py-2 d-flex align-center gap-2">
      <VTooltip location="top">
        <template #activator="{ props: activator }">
          <VIcon v-bind="activator" icon="tabler-info-circle" size="14" color="grey" />
        </template>
        <div class="text-caption">
          <div><span class="legend-dot" style="background:#2ecc71" /> En curso</div>
          <div><span class="legend-dot" style="background:#f1c40f" /> En cocina</div>
          <div><span class="legend-dot" style="background:#e67e22" /> Listo para servir</div>
        </div>
      </VTooltip>
      <span class="text-caption text-medium-emphasis">Actualiza cada 30s</span>
    </div>
  </div>
</template>

<style scoped>
.active-orders-panel {
  overflow: hidden;
}
.panel-header {
  border-bottom: thin solid rgba(var(--v-theme-on-surface), 0.1);
}
.panel-footer {
  border-top: thin solid rgba(var(--v-theme-on-surface), 0.1);
}
.orders-list {
  overflow-y: auto;
  min-height: 0;
}
.order-card {
  background: rgba(var(--v-theme-on-surface), 0.03);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 8px;
  cursor: pointer;
  transition: background 120ms ease, border-color 120ms ease, transform 120ms ease;
}
.order-card:hover {
  background: rgba(var(--v-theme-on-surface), 0.06);
  border-color: rgba(var(--v-theme-primary), 0.4);
}
.order-card:active {
  transform: scale(0.98);
}
.order-card.loading {
  opacity: 0.5;
  pointer-events: none;
}
.status-dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}
.legend-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-right: 4px;
  vertical-align: middle;
}
</style>
