<script lang="ts" setup>
  import type { PlanoTable } from '@/modules/seatingPlan/components/SalonPlanoVisual.vue'
  import type { Cart, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { computed, ref } from 'vue'
  import { useDisplay } from 'vuetify'
  import { useToast } from 'vue-toastification'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { usePosViewerMode } from '@/modules/pos/composables/usePosViewerMode.ts'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import GuestCountDialog from './Dialogs/GuestCountDialog.vue'
  import OrderDetailsDialog from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/OrderDetails/Index.vue'
  import PaymentDialog from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/Payment/Index.vue'
  import OrdersDrawer from '@/modules/pos/pages/admin/viewer/Pos/Drawers/Orders/Index.vue'
  import OrderPrintDialog from './Dialogs/OrderPrint/Index.vue'
  import RefundCancelDialog from './Dialogs/RefundCancelDialog.vue'
  import StartOrderDialog from './Dialogs/StartOrderDialog.vue'
  import ActiveOrdersPanel from './ActiveOrdersPanel/Index.vue'
  import CashMovementDrawer from './Drawers/CashMovement/Index.vue'
  import TableViewerDrawer from './Drawers/TableViewer/Index.vue'
  import MenuPanel from './MenuPanel/Index.vue'
  import OrderPanel from './OrderPanel/Index.vue'
  import TopActionsBar from './TopActionsBar.vue'

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
    qintrix: ReturnType<typeof useQintrix>
    hasActiveOrder: boolean
    startNewOrder: (opts?: { table?: Record<string, any> | null, guestCount?: number }) => Promise<void>
  }>()

  const emit = defineEmits<{
    (e: 'init-order', response: Record<string, any>): void
    (e: 'reset', cart?: Cart): void
  }>()
  const { can } = useAuth()
  const { t } = useI18n()
  const toast = useToast()
  const { edit: editOrder } = useOrder()
  const { mode: viewerMode } = usePosViewerMode()

  const canOrders = can('admin.orders.upcoming') || can('admin.orders.active')
  const showOrdersDrawer = ref(false)
  const showCashMovementDrawer = ref(false)
  const showTableViewerDrawer = ref(false)
  const showStartOrderDialog = ref(false)
  const showPlanoGuestCountDialog = ref(false)
  const pendingPlanoTable = ref<PlanoTable | null>(null)

  // Responsive: lg+ mantiene las 3 columnas inline; md-and-down mueve el
  // ActiveOrdersPanel a un drawer lateral invocado con boton del TopBar.
  const display = useDisplay()
  const isNarrow = computed(() => !display.lgAndUp.value)
  const showActiveOrdersDrawer = ref(false)

  const paymentDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const refundCancelDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const viewOrderDetailsDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const showOrderPrintDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const ordersDrawerRef = ref()
  const tableViewerDrawerRef = ref()
  const menuPanelRef = ref<any>(null)

  const onFocusMenuSearch = () => menuPanelRef.value?.focusSearch?.()

  const onClickAction = (action: string) => {
    if ((action == 'orders' || action == 'search_order') && canOrders) {
      showOrdersDrawer.value = true
    } else if (action == 'manage_cash_movement' && can('admin.pos_cash_movements.create')) {
      showCashMovementDrawer.value = true
    } else if (action == 'table_viewer' && can('admin.tables.viewer')) {
      showTableViewerDrawer.value = true
    } else if (action == 'more_print' && can('admin.orders.print')) {
      // El menu "Más acciones > Imprimir" solo funciona en edit mode
      // (requiere orderId). En create mode el toast "Próximamente" es
      // misleading pero el caso no es frecuente y hace fallback ok.
      const orderId = props.meta.order?.id
      if (orderId) {
        showOrderPrintDialog.value.orderId = orderId
        showOrderPrintDialog.value.open = true
      } else {
        toast.info(t('pos::pos_viewer.more_actions.print_needs_order'))
      }
    }
  }

  // "+ Nueva" bifurca por modo del viewer:
  //   - Modo Mesas: abrimos el dialog de seleccion (abrir mesa vs rapida).
  //   - Modo Rapido: vamos directo, sin dialog de por medio.
  const onNewOrder = () => {
    if (viewerMode.value === 'tables') {
      showStartOrderDialog.value = true
    } else {
      props.startNewOrder()
    }
  }

  const onStartOrderQuick = () => props.startNewOrder()

  const onStartOrderOpenTable = () => {
    if (can('admin.tables.viewer')) {
      showTableViewerDrawer.value = true
    }
  }

  // Mesa libre clickeada desde el plano: pedimos comensales antes de abrir.
  const onPlanoPickFree = (table: PlanoTable) => {
    pendingPlanoTable.value = table
    showPlanoGuestCountDialog.value = true
  }

  const onPlanoGuestCountConfirm = async (guestCount: number) => {
    const tbl = pendingPlanoTable.value
    if (!tbl) return
    await props.startNewOrder({
      table: {
        id: tbl.id,
        name: tbl.name,
      },
      guestCount,
    })
    pendingPlanoTable.value = null
  }

  // Mesa ocupada: cargamos la orden activa y la abrimos en el panel derecho.
  const onPlanoPickOccupied = async (table: PlanoTable) => {
    const orderId = table.active_order?.id
    if (!orderId) return
    try {
      const response = (await editOrder(props.cart.cartId, orderId)).data.body
      emit('init-order', response)
    } catch (err: any) {
      toast.error(err?.response?.data?.message ?? t('core::errors.an_unexpected_error_occurred'))
    }
  }

  const storePayment = (orderId: number | string) => {
    if (can('admin.orders.receive_payment')) {
      paymentDialog.value.orderId = orderId
      paymentDialog.value.open = true
    }
  }

  const paymentAdded = () => {
    if (showOrdersDrawer.value) {
      ordersDrawerRef.value?.refresh()
    } else if (showTableViewerDrawer.value) {
      tableViewerDrawerRef.value?.refreshTableDetails()
    }
  }

  const refundCancel = (action: string, orderId: number | string) => {
    if (can(`admin.orders.${action}`)) {
      refundCancelDialog.value.orderId = orderId
      refundCancelDialog.value.open = true
    }
  }

  const viewOrderDetails = (orderId: number | string) => {
    if (can('admin.orders.show')) {
      viewOrderDetailsDialog.value.orderId = orderId
      viewOrderDetailsDialog.value.open = true
    }
  }

  const orderPrint = (orderId: number | string) => {
    if (can('admin.orders.print')) {
      showOrderPrintDialog.value.orderId = orderId
      showOrderPrintDialog.value.open = true
    }
  }

  const resolvedRefundCancel = () => {
    if (showOrdersDrawer.value) {
      ordersDrawerRef.value?.refresh()
    } else if (showTableViewerDrawer.value) {
      tableViewerDrawerRef.value?.refreshTableDetails()
    }
  }
</script>

<template>
  <!-- Layout 3 columnas: ActiveOrders 25% | Menu 42% | Order 33% en desktop.
       La TopActionsBar vive arriba de todo como sibling de la VRow; contiene
       el toggle Mesas/Rápido y las 4 acciones globales del viewer. -->
  <div class="pos-viewer-layout d-flex flex-column">
    <TopActionsBar
      :is-narrow="isNarrow"
      :meta="meta"
      @on-click-action="onClickAction"
      @open-active-orders="showActiveOrdersDrawer = true"
    />
    <VRow class="pos-wrapper flex-grow-1" dense>
    <VCol v-if="!isNarrow" cols="12" md="3">
      <VCard class="pos-col-card">
        <ActiveOrdersPanel
          :branch-id="form.branchId"
          :cart-id="cart.cartId"
          @init-order="(response:Record<string, any>) => $emit('init-order', response)"
          @new-order="onNewOrder"
        />
      </VCard>
    </VCol>
    <VCol cols="12" md="7" lg="5">
      <VCard class="pos-col-card">
        <VCardText class="pa-3">
          <MenuPanel
            ref="menuPanelRef"
            :cart="cart"
            :form="form"
            :has-active-order="hasActiveOrder"
            :meta="meta"
            @pick-table-free="onPlanoPickFree"
            @pick-table-occupied="onPlanoPickOccupied"
          />
        </VCardText>
      </VCard>
    </VCol>
    <VCol cols="12" md="5" lg="4">
      <VCard class="pos-col-card">
        <VCardText class="pa-3">
          <OrderPanel
            :cart="cart"
            :form="form"
            :has-active-order="hasActiveOrder"
            :meta="meta"
            :qintrix="qintrix"
            @focus-menu-search="onFocusMenuSearch"
            @new-order="onNewOrder"
            @on-click-action="onClickAction"
            @reset="(cartData?:Cart)=>$emit('reset',cartData)"
            @store-payment="storePayment"
          />
        </VCardText>
      </VCard>
    </VCol>
    </VRow>
  </div>
  <!-- En pantallas md-and-down, ActiveOrdersPanel pasa a drawer lateral.
       Se abre con el botón "☰ Comandas" del TopActionsBar. -->
  <VNavigationDrawer
    v-if="isNarrow"
    v-model="showActiveOrdersDrawer"
    location="left"
    temporary
    :width="340"
  >
    <ActiveOrdersPanel
      :branch-id="form.branchId"
      :cart-id="cart.cartId"
      @init-order="(response:Record<string, any>) => { showActiveOrdersDrawer = false; $emit('init-order', response) }"
      @new-order="() => { showActiveOrdersDrawer = false; onNewOrder() }"
    />
  </VNavigationDrawer>
  <StartOrderDialog
    v-model="showStartOrderDialog"
    @open-table-viewer="onStartOrderOpenTable"
    @quick="onStartOrderQuick"
  />
  <GuestCountDialog
    v-model="showPlanoGuestCountDialog"
    :initial="1"
    @confirm="onPlanoGuestCountConfirm"
  />
  <OrderDetailsDialog
    v-if="can('admin.orders.show') && viewOrderDetailsDialog.open"
    v-model="viewOrderDetailsDialog.open"
    :order-id="viewOrderDetailsDialog.orderId"
  />
  <CashMovementDrawer
    v-if="can('admin.pos_cash_movements.create')"
    v-model="showCashMovementDrawer"
    :meta="meta"
    :register-id="form.registerId"
  />
  <TableViewerDrawer
    v-if="can('admin.tables.viewer')"
    ref="tableViewerDrawerRef"
    v-model="showTableViewerDrawer"
    :branch-id="form.branchId"
    :cart="cart"
    :form="form"
    :qintrix="qintrix"
    @cancel-order="(orderId:number|string)=>refundCancel('cancel',orderId)"
    @init-order="(response:Record<string, any>)=>$emit('init-order',response)"
    @print-order="(orderId:number|string)=>orderPrint(orderId)"
    @refund-order="(orderId:number|string)=>refundCancel('refund',orderId)"
    @store-payment="storePayment"
    @view-order="(orderId:number|string)=>viewOrderDetails(orderId)"
  />
  <OrdersDrawer
    v-if="canOrders"
    ref="ordersDrawerRef"
    v-model="showOrdersDrawer"
    :branch-id="form.branchId"
    :cart-id="cart.cartId"
    :qintrix="qintrix"
    @cancel-order="(orderId:number|string)=>refundCancel('cancel',orderId)"
    @init-order="(response:Record<string, any>)=>$emit('init-order',response)"
    @print-order="(orderId:number|string)=>orderPrint(orderId)"
    @refund-order="(orderId:number|string)=>refundCancel('refund',orderId)"
    @store-payment="storePayment"
    @view-order="(orderId:number|string)=>viewOrderDetails(orderId)"
  />
  <PaymentDialog
    v-if="can('admin.orders.receive_payment') && form.sessionId && form.registerId && paymentDialog.orderId != null && paymentDialog.open"
    v-model="paymentDialog.open"
    :order-id="paymentDialog.orderId"
    :qintrix="qintrix"
    :register-id="form.registerId"
    :session-id="form.sessionId"
    @payment-added="paymentAdded"
  />
  <RefundCancelDialog
    v-if="(can('admin.orders.cancel')||can('admin.orders.refund')) && form.registerId && refundCancelDialog.orderId != null && refundCancelDialog.open"
    v-model="refundCancelDialog.open"
    :order-id="refundCancelDialog.orderId"
    :register-id="form.registerId"
    @resolved="resolvedRefundCancel"
  />
  <OrderPrintDialog
    v-if="can('admin.orders.print') && form.registerId && form.branchId && showOrderPrintDialog.orderId != null && showOrderPrintDialog.open"
    v-model="showOrderPrintDialog.open"
    :branch-id="form.branchId"
    :order-id="showOrderPrintDialog.orderId"
    :register-id="form.registerId"
  />
</template>

<style lang="scss" scoped>
.pos-viewer-layout {
  height: calc(100vh - var(--v-layout-navbar-height, 72px));
  overflow: hidden;
  min-height: 0;
}

.pos-wrapper {
  overflow: hidden;
  min-height: 0;
  flex: 1 1 auto;
}

.pos-col-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.pos-col-card > :deep(.v-card-text) {
  flex: 1;
  overflow-y: auto;
  min-height: 0;
}
</style>
