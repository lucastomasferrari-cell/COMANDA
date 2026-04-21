<script lang="ts" setup>
  import type { Cart, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import Actions from './Actions.vue'
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
  }>()

  const emit = defineEmits<{
    (e: 'order-placed'): void
    (e: 'on-click-action', value: string): void
    (e: 'store-payment', value: string | number): void
    (e: 'reset', cart?: Cart): void
  }>()

  const { t } = useI18n()
  const toast = useToast()
  const { getPrintMeta, printPreview, store, update } = useOrder()
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
</script>

<template>
  <VRow style="height: 91vh;">
    <VCol cols="12" md="7">
      <OrderTypes
        :cart="cart"
        :form="form"
        :meta="meta"
        @on-click-action="(action:string)=>$emit('on-click-action',action)"
      />
      <Header :cart="cart" :form="form" :meta="meta" />
      <CartItems :cart="cart" :form="form" />
    </VCol>
    <VCol cols="12" md="5">
      <Actions
        :form="form"
        :meta="meta"
        @on-click-action="(action:string)=>$emit('on-click-action',action)"
      />
      <TableInfo v-if="form.table != null" :cart="cart" :form="form" :meta="meta" />
      <AdditionalInformation :cart="cart" :form="form" />
      <Discount :cart="cart" :meta="meta" />
      <Invoice :amount-due="amountDue" :cart="cart" :is-edit-mode="isEditMode" />
      <VRow class="mt-2" dense>
        <VCol v-if="hasRefundAmount" cols="12">
          <RefundPaymentMethod :form="form" :meta="meta" :overpaid-amount="overpaidAmount" />
        </VCol>
        <VCol
          v-if="!isEditMode"
          cols="12"
          :md="canReceivePayment && data.orderType?.id != 'dine_in'?6:12"
        >
          <VBtn
            block
            color="primary"
            :disabled="isSubmitDisabled||submitState.action != null"
            :loading="submitState.isLoading && submitState.action =='send_to_kitchen'"
            size="x-large"
            @click="submit('send_to_kitchen')"
          >
            <VIcon icon="tabler-chef-hat" start />
            {{ t('pos::pos_viewer.send_to_kitchen') }}
          </VBtn>
        </VCol>
        <VCol
          v-if="data.orderType?.id != 'dine_in' && (!isEditMode || isEditMode && overpaidAmount<0) && canReceivePayment"
          cols="12"
          :md="isEditMode && overpaidAmount<0?12:6"
        >
          <VBtn
            block
            color="success"
            :disabled="isSubmitDisabled||submitState.action != null"
            :loading="submitState.isLoading && submitState.action =='pay_and_fire'"
            size="x-large"
            @click="submit('pay_and_fire')"
          >
            <VIcon icon="tabler-flame" start />
            {{ t('pos::pos_viewer.pay_and_fire') }}
          </VBtn>
        </VCol>
        <VCol cols="12" md="6">
          <VBtn
            block
            color="error"
            :disabled="data.items.length === 0"
            :loading="loadingCancelOrder"
            size="x-large"
            @click="cancelOrder"
          >
            <VIcon icon="tabler-trash" start />
            {{ t(`pos::pos_viewer.${isEditMode ? "cancel_edit" : "cancel_order"}`) }}
          </VBtn>
        </VCol>
        <VCol v-if="!isEditMode" cols="12" md="6">
          <VBtn
            block
            color="warning"
            :disabled="isSubmitDisabled||submitState.action != null"
            :loading="submitState.isLoading && submitState.action =='hold_order'"
            size="x-large"
            @click="submit('hold_order')"
          >
            <VIcon icon="tabler-clock-pause" start />
            {{ t('pos::pos_viewer.hold_order') }}
          </VBtn>
        </VCol>
        <VCol v-if="isEditMode" cols="12" md="6">
          <VBtn
            block
            color="primary"
            :disabled="isSubmitDisabled||submitState.action != null"
            :loading="submitState.isLoading && submitState.action =='send_to_kitchen'"
            size="x-large"
            @click="submit('send_to_kitchen')"
          >
            <VIcon icon="tabler-chef-hat" start />
            {{ t('pos::pos_viewer.send_to_kitchen') }}
          </VBtn>
        </VCol>
      </VRow>
    </VCol>
  </VRow>

</template>
