<script lang="ts" setup>
  import type { Cart, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { ref } from 'vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import OrderDetailsDialog from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/OrderDetails/Index.vue'
  import PaymentDialog from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/Payment/Index.vue'
  import OrdersDrawer from '@/modules/pos/pages/admin/viewer/Pos/Drawers/Orders/Index.vue'
  import OrderPrintDialog from './Dialogs/OrderPrint/Index.vue'
  import RefundCancelDialog from './Dialogs/RefundCancelDialog.vue'
  import CashMovementDrawer from './Drawers/CashMovement/Index.vue'
  import TableViewerDrawer from './Drawers/TableViewer/Index.vue'
  import MenuPanel from './MenuPanel/Index.vue'
  import OrderPanel from './OrderPanel/Index.vue'

  defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
    qintrix: ReturnType<typeof useQintrix>
  }>()

  defineEmits<{
    (e: 'init-order', response: Record<string, any>): void
    (e: 'reset', cart?: Cart): void
  }>()
  const { can } = useAuth()

  const canOrders = can('admin.orders.upcoming') || can('admin.orders.active')
  const showOrdersDrawer = ref(false)
  const showCashMovementDrawer = ref(false)
  const showTableViewerDrawer = ref(false)

  const paymentDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const refundCancelDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const viewOrderDetailsDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const showOrderPrintDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const ordersDrawerRef = ref()
  const tableViewerDrawerRef = ref()

  const onClickAction = (action: string) => {
    if (action == 'orders' && canOrders) {
      showOrdersDrawer.value = true
    } else if (action == 'manage_cash_movement' && can('admin.pos_cash_movements.create')) {
      showCashMovementDrawer.value = true
    } else if (action == 'table_viewer' && can('admin.tables.viewer')) {
      showTableViewerDrawer.value = true
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
  <VRow class="pos-wrapper" dense>
    <VCol cols="12" md="4">
      <VCard height="100%">
        <VCardText class="pa-3">
          <MenuPanel :cart="cart" :form="form" :meta="meta" />
        </VCardText>
      </VCard>
    </VCol>
    <VCol cols="12" md="8">
      <VCard height="91vh">
        <VCardText class="pa-3">
          <OrderPanel
            :cart="cart"
            :form="form"
            :meta="meta"
            :qintrix="qintrix"
            @on-click-action="onClickAction"
            @reset="(cartData?:Cart)=>$emit('reset',cartData)"
            @store-payment="storePayment"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
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
.pos-wrapper {
  height: calc(100vh - var(--v-layout-navbar-height, 72px));
  overflow: hidden;
  min-height: 0;
}
</style>
