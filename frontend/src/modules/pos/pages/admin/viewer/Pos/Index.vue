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
  import { useOrder } from '@/modules/sale/composables/order.ts'
  // GuestCountDialog mount eliminado Sprint 2 B.3 — tap en mesa abre
  // directo con guestCount=1 default, edición inline en panel derecho.
  // Archivo vive huérfano por si se revierte.
  import OrderDetailsDialog from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/OrderDetails/Index.vue'
  import PaymentDialog from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/Payment/Index.vue'
  // OrdersDrawer removido del mount (Sprint 1.A): ActiveOrdersPanel
  // izquierdo permanente cumple la misma función. Archivo vive huérfano
  // por si se reactiva en una vista dedicada (/admin/pos/comandas).
  import OrderPrintDialog from './Dialogs/OrderPrint/Index.vue'
  import RefundCancelDialog from './Dialogs/RefundCancelDialog.vue'
  // StartOrderDialog eliminado del mount (Sprint 2 B.1): el modal
  // bifurcador quedó redundante con el nuevo split-screen (plano = abrir
  // mesa, botón + = orden rápida). Archivo vive huérfano.
  import ActiveOrdersPanel from './ActiveOrdersPanel/Index.vue'
  import CajaMode from './CajaMode.vue'
  // Drawers/Caja eliminado Sprint 3.A.bis — el contenido migró a CajaMode
  // como modo propio del switcher vertical. Ver commit hash del sprint.
  import TableViewerDrawer from './Drawers/TableViewer/Index.vue'
  import MenuPanel from './MenuPanel/Index.vue'
  import ModeSwitcher from './ModeSwitcher.vue'
  import OrderPanel from './OrderPanel/Index.vue'
  // TopActionsBar eliminado Sprint 3.A.bis — "+ Orden rápida" se reubica
  // en el modo Mostrador; "Caja" es modo propio en el switcher.
  import { usePosMode } from '@/modules/pos/composables/posMode.ts'

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

  // showCajaDrawer eliminado Sprint 3.A.bis — Caja es modo propio.
  const showTableViewerDrawer = ref(false)
  // Count emitido por TablePlano tras fetch. Lo usa el StartOrderDialog
  // para disabiliar "Abrir mesa" con tooltip cuando no hay mesas cargadas.
  const tablesCount = ref(0)

  // Responsive: lg+ mantiene las 3 columnas inline; md-and-down mueve el
  // ActiveOrdersPanel a un drawer lateral invocado con boton del TopBar.
  const display = useDisplay()
  const isNarrow = computed(() => !display.lgAndUp.value)
  const showActiveOrdersDrawer = ref(false)

  // Sprint 3.A — switcher vertical de modos. Lee feature_flags del meta
  // (inyectado desde /pos/configuration). Si solo 1 modo está activo,
  // showSwitcher=false y el componente no se monta; el layout toma todo
  // el ancho útil sin la columna de 80px. El modo activo se persiste
  // en localStorage vía usePosMode. El render específico de cada modo
  // (Salón ≠ Mostrador ≠ Pedidos) se implementa en Sprint 3.B/3.C.
  const {
    mode: posMode,
    availableModes,
    showSwitcher,
  } = usePosMode(() => (props.meta as any)?.feature_flags?.pos ?? null)

  const paymentDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const refundCancelDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const viewOrderDetailsDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const showOrderPrintDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const tableViewerDrawerRef = ref()
  const menuPanelRef = ref<any>(null)

  const onFocusMenuSearch = () => menuPanelRef.value?.focusSearch?.()

  const onClickAction = (action: string) => {
    if (action == 'manage_cash_movement' && can('admin.pos_cash_movements.create')) {
      // Manage cash movement pasa al modo Caja del switcher.
      posMode.value = 'caja'
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

  // "+ Nueva" del panel izq y "+ Orden rápida" del topheader (Sprint 2 B.4):
  // van DIRECTO a crear orden sin mesa (takeaway/mostrador). El modal
  // bifurcador StartOrderDialog que antes preguntaba "¿Cómo querés
  // arrancar?" quedó eliminado (Sprint 2 B.1) — la bifurcación ya se
  // resuelve por la topología del layout: el plano para abrir mesa, el
  // botón + para orden rápida. OrderTypes.watch elige el primer canal
  // non-dine_in disponible como default.
  const onNewOrder = () => props.startNewOrder()

  // Sprint 2 B.3 — Mesa libre clickeada desde el plano: apertura directa
  // con guestCount=1 default. Antes pasaba por GuestCountDialog (modal con
  // +/- antes de abrir) → 2 taps extras por mesa. El mozo edita el
  // guestCount inline en el panel derecho después (commit B.2); si se
  // olvida, la validación al enviar a cocina (B.5) lo ataja.
  const onPlanoPickFree = async (table: PlanoTable) => {
    await props.startNewOrder({
      table: { id: table.id, name: table.name },
      guestCount: 1,
    })
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
    if (showTableViewerDrawer.value) {
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
    if (showTableViewerDrawer.value) {
      tableViewerDrawerRef.value?.refreshTableDetails()
    }
  }
</script>

<template>
  <!-- Layout 3 columnas: ActiveOrders 25% | Menu 42% | Order 33% en desktop.
       La TopActionsBar vive arriba de todo como sibling de la VRow; contiene
       el toggle Mesas/Rápido y las 4 acciones globales del viewer. -->
  <div class="pos-viewer-layout d-flex flex-column">
    <!-- Sprint 3.A — wrapper del layout con el switcher vertical a la
         izquierda (si hay más de 1 modo activo). El comportamiento
         específico de cada modo (Salón ≠ Mostrador ≠ Pedidos) se
         introduce en 3.B y 3.C; por ahora el switcher se monta y
         recuerda el modo en localStorage. El Salón sigue siendo el
         renderizado default del layout en los 3 modos hasta 3.B. -->
    <div class="pos-layout-wrapper flex-grow-1 d-flex">
      <ModeSwitcher
        v-if="showSwitcher"
        v-model="posMode"
        :available-modes="availableModes"
      />

      <!-- Modo Caja: pantalla completa propia, toma el espacio a la
           derecha del switcher. Sprint 3.A.bis — reemplaza el CajaDrawer. -->
      <CajaMode
        v-if="posMode === 'caja' && can('admin.pos_cash_movements.create')"
        class="flex-grow-1"
        :meta="meta"
        :register-id="form.registerId"
        :session-id="form.sessionId"
        @session-closed="$emit('reset')"
      />

    <!-- Modos salón/counter/orders — split-screen con CSS Grid.
         Vista "home" (!hasActiveOrder): 2 columnas 30/70 — ActiveOrders
         + plano de mesas ocupando todo el espacio.
         Vista "working" (hasActiveOrder): 3 columnas 22/48/30 —
         ActiveOrders colapsado + grid productos + detalle comanda. -->
    <div
      v-else
      class="pos-layout flex-grow-1"
      :class="{ 'pos-layout--working': hasActiveOrder, 'pos-layout--narrow': isNarrow }"
    >

      <aside v-if="!isNarrow" class="pos-panel pos-panel--orders">
        <VCard class="pos-col-card">
          <ActiveOrdersPanel
            :active-order-id="meta.order?.id ?? null"
            :branch-id="form.branchId"
            :cart-id="cart.cartId"
            :collapsed="hasActiveOrder"
            @init-order="(response:Record<string, any>) => $emit('init-order', response)"
            @new-order="onNewOrder"
          />
        </VCard>
      </aside>
      <main class="pos-panel pos-panel--main">
        <VCard class="pos-col-card">
          <VCardText class="pa-3">
            <MenuPanel
              ref="menuPanelRef"
              :cart="cart"
              :form="form"
              :has-active-order="hasActiveOrder"
              :meta="meta"
              @init-order="(response:Record<string, any>) => $emit('init-order', response)"
              @pick-table-free="onPlanoPickFree"
              @pick-table-occupied="onPlanoPickOccupied"
              @tables-count="(count:number) => tablesCount = count"
            />
          </VCardText>
        </VCard>
      </main>
      <aside v-if="hasActiveOrder" class="pos-panel pos-panel--cart">
        <VCard class="pos-col-card">
          <VCardText class="pa-3">
            <OrderPanel
              :cart="cart"
              :form="form"
              :has-active-order="hasActiveOrder"
              :meta="meta"
              :qintrix="qintrix"
              @focus-menu-search="onFocusMenuSearch"
              @on-click-action="onClickAction"
              @reset="(cartData?:Cart)=>$emit('reset',cartData)"
              @store-payment="storePayment"
            />
          </VCardText>
        </VCard>
      </aside>
    </div>
    </div>
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
  <!-- StartOrderDialog + GuestCountDialog mounts eliminados Sprint 2 B.1/B.3.
       Bifurcación (mesa vs orden rápida) resuelta por topología del layout;
       comensales se edita inline en AdditionalInformation con stepper +/-. -->
  <OrderDetailsDialog
    v-if="can('admin.orders.show') && viewOrderDetailsDialog.open"
    v-model="viewOrderDetailsDialog.open"
    :order-id="viewOrderDetailsDialog.orderId"
  />
  <!-- CajaDrawer eliminado — caja es modo propio (CajaMode) invocado
       desde el switcher vertical. Ver commit del Sprint 3.A.bis. -->
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
  <!-- OrdersDrawer mount removido Sprint 1.A — componente huérfano preservado -->
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

/* Sprint 2 A.1 — layout split-screen con CSS Grid. 2 vistas:
   - home (sin comanda activa): 30/70 — ActiveOrders + plano.
   - working (con comanda): 22/48/30 — ActiveOrders colapsado + productos + comanda. */
/* Sprint 3.A — wrapper horizontal: ModeSwitcher (80px) + .pos-layout. */
.pos-layout-wrapper {
  overflow: hidden;
  min-height: 0;
}

.pos-layout {
  flex: 1 1 auto;
  display: grid;
  grid-template-columns: 30% 70%;
  gap: 8px;
  overflow: hidden;
  min-height: 0;
}
.pos-layout--working {
  grid-template-columns: 22% 48% 30%;
}
/* Sub-1024: md-and-down ya oculta ActiveOrders (pasa a drawer). Ese
   caso se controla con --narrow: una sola columna con el MenuPanel.
   Cuando hay comanda activa, a partir de md se muestran 2 columnas:
   productos arriba / comanda abajo — las cards se apilan con auto-flow.
   En un iteración futura se puede reemplazar por tabs. */
.pos-layout--narrow {
  grid-template-columns: 1fr;
}
.pos-layout--narrow.pos-layout--working {
  grid-template-columns: 1fr;
  grid-auto-rows: minmax(0, 1fr);
}
.pos-panel {
  min-width: 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.pos-col-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
/* El v-card-text NO scrollea: es solo el contenedor flex para que el
   OrderPanel/ActiveOrdersPanel adentro controlen su propio scroll interno
   (items-container) con el footer sticky. Si ponemos overflow-y:auto acá,
   los botones Marchar/Cobrar se scrollean fuera del viewport cuando la
   orden tiene muchos items. */
.pos-col-card > :deep(.v-card-text) {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}
</style>
