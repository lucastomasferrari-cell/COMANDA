<script lang="ts" setup>
  import type { Cart, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import AdditionalInformation from './AdditionalInformation.vue'
  import CartItems from './CartItems/Index.vue'
  import Discount from './Discount.vue'
  import Header from './Header.vue'
  import Invoice from './Invoice.vue'
  import OrderTypes from './OrderTypes.vue'
  import RefundPaymentMethod from './RefundPaymentMethod.vue'
  import TableInfo from './TableInfo.vue'

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
    qintrix: ReturnType<typeof useQintrix>
    hasActiveOrder: boolean
  }>()

  const emit = defineEmits<{
    (e: 'order-placed'): void
    (e: 'on-click-action', value: string): void
    (e: 'store-payment', value: string | number): void
    (e: 'reset', cart?: Cart): void
    (e: 'focus-menu-search'): void
  }>()

  const { t } = useI18n()
  const toast = useToast()
  const { getPrintMeta, printPreview, store, update, requestBill, pause } = useOrder()
  const { can } = useAuth()

  const { processing, data, clear, cartId } = props.cart
  const { refundPaymentMethod } = toRefs(props.form)
  const { order } = toRefs(props.meta)
  const canReceivePayment = computed(() => can('admin.orders.receive_payment'))
  const isEditMode = computed(() => props.form.mode === 'edit')

  const overpaidAmount = computed(() => {
    if (isEditMode.value && order?.value != null) {
      const amountPaid = order.value.total.amount - order.value.due_amount.amount
      return Number.parseFloat((amountPaid - data.value.total.amount).toFixed(order.value.due_amount.precision))
    }
    refundPaymentMethod.value = null
    return 0
  })

  const hasRefundAmount = computed(() => overpaidAmount.value > 0 && isEditMode.value)
  const loadingCancelOrder = ref<boolean>(false)
  const isSubmitDisabled = computed(() => {
    return data.value.items.length === 0
      || !data.value.orderType?.id
      || !props.form.registerId
      || (isEditMode.value && overpaidAmount.value > 0 && !props.form.refundPaymentMethod)
  })

  // Mostrar "Cobrar" cuando el canal no es dine_in (salón no paga en caja
  // de la comanda) y el user puede recibir pagos. No bloquea en edit mode
  // si hubo overpaid negativo (queda solo el Enviar a cocina ampliado).
  const canShowPay = computed(() =>
    data.value.orderType?.id !== 'dine_in'
    && canReceivePayment.value
    && (!isEditMode.value || overpaidAmount.value < 0),
  )

  const submitState = ref<{
    action: null | 'send_to_kitchen' | 'hold_order' | 'pay_and_fire'
    isLoading: boolean
  }>({
    action: null,
    isLoading: false,
  })

  async function submit (action: 'send_to_kitchen' | 'hold_order' | 'pay_and_fire') {
    if (processing.value || isSubmitDisabled.value) {
      return
    }

    // Sprint 2 B.5 — validación de guest_count al ENVIAR A COCINA (no al abrir).
    // Aplica solo a dine_in (orden con mesa). Takeaway / drive-thru / pre-order
    // no cuentan comensales. hold_order (pausa) tampoco valida — es un save
    // intermedio. pay_and_fire sí valida porque también fire la comanda.
    if (action !== 'hold_order' && props.form.table?.id) {
      const gc = Number(props.form.meta.guestCount ?? 0)
      if (!Number.isFinite(gc) || gc < 1) {
        toast.warning(t('pos::pos_viewer.guest_count_required'))
        return
      }
    }

    submitState.value = { action, isLoading: true }
    if (await placeOrder(action)) {
      emit('order-placed')
    }
    submitState.value = { action: null, isLoading: false }
  }

  async function placeOrder (action: 'send_to_kitchen' | 'hold_order' | 'pay_and_fire'): Promise<boolean> {
    try {
      const params = {
        submit_action: action,
        menu_id: props.form.menuId,
        branch_id: props.form.branchId,
        guest_count: props.form.meta.guestCount,
        register_id: props.form.registerId,
        waiter_id: props.form.waiter?.id,
        session_id: props.form.sessionId,
        table_id: props.form.table?.id,
        notes: props.form.meta.notes,
        refund_payment_method: isEditMode.value && overpaidAmount.value > 0 ? props.form.refundPaymentMethod : null,
        car_plate: data.value.orderType.id == 'drive_thru' ? props.form.meta.carPlate : null,
        car_description: data.value.orderType.id == 'drive_thru' ? props.form.meta.carDescription : null,
        scheduled_at: ['pre_order', 'catering'].includes((data.value.orderType.id as string)) ? props.form.meta.scheduledAt : null,
      }
      const response = await (props.form.mode == 'edit'
        ? update(cartId, props.cart.data.value.order?.id as number, params)
        : store(cartId, params))

      const orderId = response.data.body.order_id
      const isScheduledOrder = !!params.scheduled_at
      const isScheduledForTodayOrder = isScheduledForToday(params.scheduled_at)
      const isHeldOrderStatusChange = isEditMode.value
        && order.value?.status?.id === 'pending'
        && action === 'send_to_kitchen'
      const isPendingAfterSubmit = !isEditMode.value && action !== 'send_to_kitchen'
      const shouldPrintScheduledTodayEditOrder = isEditMode.value
        && action === 'send_to_kitchen'
        && isScheduledForTodayOrder
        && order.value?.status?.id !== 'pending'

      if (!isEditMode.value || isHeldOrderStatusChange || shouldPrintScheduledTodayEditOrder) {
        await printCreatedOrder(orderId, action, isScheduledOrder, isScheduledForTodayOrder, isPendingAfterSubmit)
      }

      if (action == 'pay_and_fire') {
        emit('store-payment', orderId)
      } else if (response.data?.message) {
        toast.success(response.data.message)
      }

      emit('reset', response.data.body.cart)
      return true
    } catch (error: any) {
      if (error?.response?.status === 401) {
        toast.error(t('core::errors.unauthorized'))
      } else if (error.response?.data?.message) {
        const data = error.response.data
        const errors = data.errors
        const errorKeys = typeof data.errors === 'object' ? Object.keys(errors) : []
        const firstKey = errorKeys.length > 0 ? errorKeys[0] : null
        const firstError = firstKey && errors ? errors[firstKey]?.[0] : null
        toast.error(firstError || data.message)
      } else {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }

      return false
    }
  }

  async function printCreatedOrder (
    orderId: string | number,
    action: 'send_to_kitchen' | 'hold_order' | 'pay_and_fire',
    isScheduledOrder: boolean,
    isScheduledForTodayOrder: boolean,
    isPendingAfterSubmit: boolean,
  ) {
    try {
      if (!props.qintrix.isSetup.value || !props.form.registerId) {
        return
      }

      const response = (await getPrintMeta(orderId, props.form.branchId, props.form.registerId)).data.body
      const availableContents = Object.values(response.contents || {}) as Record<string, any>[]

      const billContents = availableContents.filter(content => content?.type?.id === 'bill')
      const kitchenContents = availableContents.filter(content => content?.type?.id === 'kitchen')
      let printQueue: Record<string, any>[] = []
      const shouldPrintKitchenForScheduledToday = isScheduledForTodayOrder && !isPendingAfterSubmit

      if (props.form.mode === 'edit') {
        printQueue = kitchenContents
      } else if (action === 'hold_order' || (isScheduledOrder && !shouldPrintKitchenForScheduledToday)) {
        printQueue = billContents
      } else if (action === 'pay_and_fire') {
        printQueue = kitchenContents
      } else {
        printQueue = [...kitchenContents, ...billContents]
      }

      for (const content of printQueue) {
        const preview = (await printPreview(
          orderId,
          content.type.id,
          content.type.id === 'kitchen' ? content.id : undefined,
          props.form.registerId,
          'pdf',
        )).data.body

        if (preview.printer?.qintrix_id && preview.printer?.paper_size) {
          await props.qintrix.createJob(
            preview.content,
            preview.printer.qintrix_id,
            preview.printer.paper_size,
            `${orderId}`,
            `order-${content.type.id}`,
            'pdf',
          )
        }
      }
    } catch (error: any) {
      toast.error(error?.response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
    }
  }

  async function requestBillForOrder () {
    const orderId = props.meta.order?.id
    if (!orderId) return
    try {
      const res = await requestBill(orderId)
      toast.success(res.data?.message ?? t('pos::pos_viewer.more_actions.bill_requested'))
    } catch (err: any) {
      toast.error(err?.response?.data?.message ?? t('core::errors.an_unexpected_error_occurred'))
    }
  }

  async function pauseCurrentOrder () {
    const orderId = props.meta.order?.id
    if (!orderId) return
    try {
      const res = await pause(orderId)
      toast.success(res.data?.message ?? t('pos::pos_viewer.more_actions.order_paused'))
      emit('reset')
    } catch (err: any) {
      toast.error(err?.response?.data?.message ?? t('core::errors.an_unexpected_error_occurred'))
    }
  }

  async function cancelOrder () {
    if (processing.value) {
      return
    }
    loadingCancelOrder.value = true
    await clear()
    emit('reset')
    loadingCancelOrder.value = false
  }

  const amountDue = computed(() => {
    const amountDue = overpaidAmount.value < 0 ? Math.abs(overpaidAmount.value) : 0
    return amountDue == Number.parseFloat(props.cart.data.value.total.amount.toFixed(props.cart.data.value.total.precision)) ? 0 : amountDue
  })

  function isScheduledForToday (scheduledAt?: string | null) {
    if (!scheduledAt) {
      return false
    }

    const scheduledDate = String(scheduledAt).slice(0, 10)
    const today = new Date().toLocaleDateString('en-CA')

    return scheduledDate <= today
  }

  // Keyboard shortcuts (Enter / Ctrl+Enter / ESC). Solo activos cuando hay
  // orden activa y el foco NO esta en un input/textarea/contenteditable —
  // asi no interferimos con la busqueda de productos ni los campos del form.
  const onGlobalKeydown = (ev: KeyboardEvent) => {
    if (!props.hasActiveOrder) return
    const target = ev.target as HTMLElement | null
    const tag = target?.tagName?.toLowerCase()
    const isTextInput = tag === 'input' || tag === 'textarea' || target?.isContentEditable
    if (ev.key === 'Escape') {
      if (isTextInput) return
      emit('focus-menu-search')
      return
    }
    if (ev.key !== 'Enter') return
    if (isTextInput) return
    if (isSubmitDisabled.value || submitState.value.action != null) return
    if (ev.ctrlKey || ev.metaKey) {
      if (!canShowPay.value) return
      ev.preventDefault()
      submit('pay_and_fire')
      return
    }
    ev.preventDefault()
    submit('send_to_kitchen')
  }

  onMounted(() => { window.addEventListener('keydown', onGlobalKeydown) })
  onBeforeUnmount(() => { window.removeEventListener('keydown', onGlobalKeydown) })
</script>

<template>
  <!-- Layout interno del OrderPanel reorganizado a 1 columna vertical.
       Antes era 2 sub-cols (7/5) que asumia pantalla ancha — con el nuevo
       layout 3-cols exterior, el OrderPanel ahora ocupa ~33% de la pantalla
       y todo tiene que ir apilado en vertical. -->
  <!-- Sin orden activa: solo empty state, sin CTA duplicado. El "+ Nueva"
       vive en ActiveOrdersPanel (columna izquierda) y es el unico
       punto de entrada; duplicarlo aca confundia al cajero (pensaba
       que eran dos flujos distintos). -->
  <div
    v-if="!hasActiveOrder"
    class="order-panel-empty d-flex flex-column align-center text-center h-100 px-4 pt-10"
  >
    <div class="empty-icon-wrap mb-3">
      <VIcon color="primary" icon="tabler-clipboard-list" size="36" />
    </div>
    <h3 class="text-subtitle-1 font-weight-medium mb-1">
      {{ t('pos::pos_viewer.no_active_order.title') }}
    </h3>
    <p class="text-body-2 text-medium-emphasis">
      {{ t('pos::pos_viewer.no_active_order.description') }}
    </p>
  </div>
  <!-- order-panel-stack con height:100% para que los children flex calculen
       espacio real. El items-container (flex-grow + overflow-y:auto) se come
       el espacio disponible y el footer queda sticky al pie. -->
  <div v-else class="order-panel-stack d-flex flex-column" style="gap: 0.5rem; height: 100%;">
    <!-- Header: info de orden (mozo/cliente) + canal.
         Las acciones globales (visor mesas, cash movement, comandas) viven
         ahora en TopActionsBar a nivel del viewer, no dentro del OrderPanel. -->
    <Header :cart="cart" :form="form" :meta="meta" @back-to-map="$emit('reset')" />
    <OrderTypes
      :cart="cart"
      :form="form"
      :meta="meta"
      @on-click-action="(action:string)=>$emit('on-click-action',action)"
    />
    <TableInfo v-if="form.table != null" :cart="cart" :form="form" :meta="meta" />
    <AdditionalInformation :cart="cart" :form="form" />

    <!-- Items del carrito — único scroll vertical del panel. flex-grow + min-height:0
         para que shrink sin colapsar, y overflow-y:auto para el scroll cuando la
         comanda crece. -->
    <div class="order-items-scroll flex-grow-1" style="overflow-y: auto; min-height: 0;">
      <CartItems :cart="cart" :form="form" />
    </div>

    <!-- Footer sticky: descuento + totales + botones grandes -->
    <div class="order-panel-footer">
      <Discount :cart="cart" :meta="meta" />
      <Invoice :amount-due="amountDue" :cart="cart" :is-edit-mode="isEditMode" />
      <VRow v-if="hasRefundAmount" class="mt-2" dense>
        <VCol cols="12">
          <RefundPaymentMethod :form="form" :meta="meta" :overpaid-amount="overpaidAmount" />
        </VCol>
      </VRow>
      <!-- Footer Toast-style: 2 botones grandes principales + 1 mas acciones -->
      <div class="order-footer-buttons mt-2">
        <div class="primary-row">
          <VBtn
            class="primary-cta"
            color="warning"
            :disabled="isSubmitDisabled||submitState.action != null"
            :loading="submitState.isLoading && submitState.action =='send_to_kitchen'"
            size="x-large"
            @click="submit('send_to_kitchen')"
          >
            <VIcon icon="tabler-chef-hat" start />
            {{ t('pos::pos_viewer.send_to_kitchen') }}
            <kbd class="shortcut-hint ms-2">↵</kbd>
          </VBtn>
          <VBtn
            v-if="canShowPay"
            class="primary-cta"
            color="success"
            :disabled="isSubmitDisabled||submitState.action != null"
            :loading="submitState.isLoading && submitState.action =='pay_and_fire'"
            size="x-large"
            @click="submit('pay_and_fire')"
          >
            <VIcon icon="tabler-cash" start />
            {{ t('pos::pos_viewer.pay_and_fire') }}
            <kbd class="shortcut-hint ms-2">⌘↵</kbd>
          </VBtn>
        </div>
        <VMenu location="top">
          <template #activator="{ props: menuProps }">
            <VBtn
              v-bind="menuProps"
              block
              class="more-actions-btn mt-2"
              color="default"
              prepend-icon="tabler-dots"
              variant="tonal"
            >
              {{ t('pos::pos_viewer.more_actions.label') }}
            </VBtn>
          </template>
          <VList density="compact">
            <VListItem
              v-if="!isEditMode"
              :disabled="isSubmitDisabled||submitState.action != null"
              @click="submit('hold_order')"
            >
              <template #prepend><VIcon color="warning" icon="tabler-clock-pause" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.hold_order') }}
              </VListItemTitle>
            </VListItem>
            <VListItem
              v-if="isEditMode && meta.order?.id"
              @click="requestBillForOrder"
            >
              <template #prepend><VIcon color="error" icon="tabler-receipt" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.more_actions.items.request_bill') }}
              </VListItemTitle>
            </VListItem>
            <VListItem
              v-if="isEditMode && meta.order?.id"
              @click="pauseCurrentOrder"
            >
              <template #prepend><VIcon color="grey" icon="tabler-player-pause" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.more_actions.items.pause_order') }}
              </VListItemTitle>
            </VListItem>
            <VListItem @click="emit('on-click-action','more_discount')">
              <template #prepend><VIcon icon="tabler-discount" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.more_actions.items.apply_discount') }}
              </VListItemTitle>
              <template #append>
                <VChip color="grey" density="compact" size="x-small">
                  {{ t('pos::pos_viewer.more_actions.coming_soon') }}
                </VChip>
              </template>
            </VListItem>
            <VListItem @click="emit('on-click-action','more_split_bill')">
              <template #prepend><VIcon icon="tabler-columns-2" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.more_actions.items.split_bill') }}
              </VListItemTitle>
              <template #append>
                <VChip color="grey" density="compact" size="x-small">
                  {{ t('pos::pos_viewer.more_actions.coming_soon') }}
                </VChip>
              </template>
            </VListItem>
            <VListItem @click="emit('on-click-action','more_change_table')">
              <template #prepend><VIcon icon="tabler-arrows-right-left" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.more_actions.items.change_table') }}
              </VListItemTitle>
              <template #append>
                <VChip color="grey" density="compact" size="x-small">
                  {{ t('pos::pos_viewer.more_actions.coming_soon') }}
                </VChip>
              </template>
            </VListItem>
            <VListItem
              :disabled="!isEditMode"
              @click="emit('on-click-action','more_print')"
            >
              <template #prepend><VIcon icon="tabler-printer" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.more_actions.items.print') }}
              </VListItemTitle>
              <template v-if="!isEditMode" #append>
                <VChip color="grey" density="compact" size="x-small">
                  {{ t('pos::pos_viewer.more_actions.print_needs_order_short') }}
                </VChip>
              </template>
            </VListItem>
            <VDivider class="my-1" />
            <VListItem
              :disabled="data.items.length === 0 || loadingCancelOrder"
              @click="cancelOrder"
            >
              <template #prepend><VIcon color="error" icon="tabler-trash" /></template>
              <VListItemTitle class="text-error">
                {{ t(`pos::pos_viewer.${isEditMode ? "cancel_edit" : "cancel_order"}`) }}
              </VListItemTitle>
            </VListItem>
          </VList>
        </VMenu>
      </div>
    </div>
  </div>
</template>

<style scoped>
.order-panel-footer {
  flex-shrink: 0;
  border-top: thin solid rgba(var(--v-theme-on-surface), 0.08);
  padding-top: 0.5rem;
}

.order-panel-empty {
  min-height: 80vh;
}

.empty-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: rgba(var(--v-theme-primary), 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
}

.order-footer-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.primary-row {
  display: flex;
  gap: 0.5rem;
}

.primary-row .primary-cta {
  flex: 1 1 0;
  min-height: 56px;
  font-size: 0.95rem;
  font-weight: 700;
  letter-spacing: 0.02em;
}

.shortcut-hint {
  background: rgba(255, 255, 255, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.28);
  border-radius: 4px;
  font-size: 0.7rem;
  font-family: inherit;
  padding: 0 4px;
  line-height: 1.2;
  opacity: 0.9;
}

.more-actions-btn {
  font-weight: 600;
}
</style>
