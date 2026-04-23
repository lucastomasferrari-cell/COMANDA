<script lang="ts" setup>
  import type { AxiosError } from 'axios'
  import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useOrder } from '@/modules/sale/composables/order.ts'

  const props = defineProps<{
    branchId: number | null
    cartId: string
  }>()

  const emit = defineEmits<{
    (e: 'init-order', response: Record<string, any>): void
    (e: 'new-order'): void
  }>()

  const { t } = useI18n()
  const { activeOrders, edit, resume } = useOrder()
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
    bill_requested_at?: string | null
    paused_at?: string | null
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
   * Color del punto segun order.status + flags bill_requested/paused.
   * bill_requested_at (rojo) y paused_at (gris) ganan sobre el status
   * base: representan un state de "alerta" o "suspension" independiente.
   *
   * Usa rgb(var(--v-theme-*)) para que el color respete dark/light mode
   * automáticamente. "grey" se construye con on-surface bajado de opacidad
   * (no hay token grey nativo dedicado para esto).
   */
  function statusColor (order: ActiveOrder): string {
    if (order.paused_at) return 'rgba(var(--v-theme-on-surface), 0.38)'
    if (order.bill_requested_at) return 'rgb(var(--v-theme-error))'
    switch (order.status) {
      case 'pending':
        return 'rgb(var(--v-theme-success))' // en curso
      case 'confirmed':
      case 'preparing':
        return 'rgb(var(--v-theme-warning))' // en cocina
      case 'ready':
        return 'rgb(var(--v-theme-table-ready))' // listo para servir (usa token custom)
      default:
        return 'rgba(var(--v-theme-on-surface), 0.38)'
    }
  }

  function statusLabel (status: string): string {
    const key = `pos::pos_viewer.active_orders.status_label.${status}`
    const translated = t(key)
    return translated === key ? status : translated
  }

  function contextLabel (o: ActiveOrder): string {
    if (o.table?.name) return o.table.name
    if (o.type === 'takeaway') return t('pos::pos_viewer.active_orders.context.takeaway')
    if (o.type === 'drive_thru') return t('pos::pos_viewer.active_orders.context.drive_thru')
    if (o.type === 'pre_order') return t('pos::pos_viewer.active_orders.context.pre_order')
    if (o.type === 'catering') return t('pos::pos_viewer.active_orders.context.catering')
    return o.customer?.name ?? t('pos::pos_viewer.active_orders.context.no_table')
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
      // Si la orden está pausada, la reanudamos antes del edit — la mesa
      // vuelve a occupied automáticamente via listener RestoreTableOnResume.
      if (order.paused_at) {
        await resume(order.id)
      }
      const response = (await edit(props.cartId, order.id)).data.body
      emit('init-order', response)
    } catch (error) {
      toast.error((error as AxiosError<{ message?: string }>).response?.data?.message ?? t('pos::pos_viewer.active_orders.error_opening'))
    } finally {
      loadingOrderId.value = null
    }
  }

  function newOrder () {
    emit('new-order')
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
        {{ t('pos::pos_viewer.active_orders.title') }}
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
        {{ t('pos::pos_viewer.active_orders.new_button') }}
      </VBtn>
    </div>

    <div v-if="loading && orders.length === 0" class="flex-grow-1 d-flex align-center justify-center">
      <VProgressCircular color="primary" indeterminate size="30" />
    </div>

    <div
      v-else-if="orders.length === 0"
      class="flex-grow-1 d-flex flex-column align-center justify-start text-center px-4 pt-8"
    >
      <div class="empty-icon-wrap mb-3">
        <VIcon color="primary" icon="tabler-inbox" size="28" />
      </div>
      <p class="text-subtitle-2 font-weight-medium mb-1">
        {{ t('pos::pos_viewer.active_orders.empty') }}
      </p>
      <p class="text-caption text-medium-emphasis">
        {{ t('pos::pos_viewer.active_orders.empty_hint') }}
      </p>
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
          <span class="status-dot me-2" :style="{ background: statusColor(order) }" />
          <VIcon
            v-if="order.paused_at"
            class="me-1"
            color="grey"
            icon="tabler-player-pause"
            size="14"
          />
          <VIcon
            v-if="order.bill_requested_at"
            class="me-1"
            color="error"
            icon="tabler-receipt"
            size="14"
          />
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
          <div><span class="legend-dot legend-dot--in-progress" /> {{ t('pos::pos_viewer.active_orders.legend.in_progress') }}</div>
          <div><span class="legend-dot legend-dot--in-kitchen" /> {{ t('pos::pos_viewer.active_orders.legend.in_kitchen') }}</div>
          <div><span class="legend-dot legend-dot--ready" /> {{ t('pos::pos_viewer.active_orders.legend.ready_to_serve') }}</div>
        </div>
      </VTooltip>
      <span class="text-caption text-medium-emphasis">{{ t('pos::pos_viewer.active_orders.refresh_note') }}</span>
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
/* Cada dot usa el mismo token semantic que statusColor() devuelve —
   evita tener la paleta duplicada entre JS y CSS. */
.legend-dot--in-progress { background: rgb(var(--v-theme-success)); }
.legend-dot--in-kitchen  { background: rgb(var(--v-theme-warning)); }
.legend-dot--ready       { background: rgb(var(--v-theme-table-ready)); }
.empty-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(var(--v-theme-primary), 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
