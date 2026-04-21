<script lang="ts" setup>
  import type { AxiosError } from 'axios'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import FinalizePayment from './FinalizePayment/Index.vue'
  import OrderSummary from './OrderSummary/Index.vue'

  const props = defineProps<{
    modelValue: boolean
    orderId: string | number
    qintrix: ReturnType<typeof useQintrix>
    registerId: number
    sessionId: number
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'payment-added'): void
  }>()

  const { getPaymentMeta, printPreview, storePayment } = useOrder()
  const toast = useToast()
  const { t } = useI18n()

  const form = useForm<{
    payments: {
      method: string | null
      amount: number | string | null
      transaction_id: string | null
      gift_card_code: string | null
    }[]
    register_id: number
    session_id: number
    payment_mode: string
    customer_given_amount: string
    change_return: string
    with_print: boolean
  }>({
    payments: [],
    register_id: props.registerId,
    session_id: props.sessionId,
    payment_mode: 'full',
    customer_given_amount: '',
    change_return: '',
    with_print: false,
  })

  const loading = ref(false)
  const meta = ref<{
    paymentMethods: { id: string, name: string }[]
    paymentModes: any[]
    quickPayAmounts: number[]
    order: Record<string, any>
  }>({
    paymentMethods: [],
    paymentModes: [],
    quickPayAmounts: [],
    order: {},
  })

  const defaultQuickPayAmounts = [10, 20, 50, 100]
  const activeInputPath = ref<string | null>(null)

  const dialogModel = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val),
  })

  const close = () => emit('update:modelValue', false)

  async function loadMeta () {
    try {
      loading.value = true
      const response = (await getPaymentMeta(props.orderId)).data.body
      meta.value.paymentMethods = response.payment_methods
      meta.value.paymentModes = response.payment_modes
      meta.value.quickPayAmounts = Array.isArray(response.quick_pay_amounts)
        ? response.quick_pay_amounts
        : defaultQuickPayAmounts
      meta.value.order = response.order
      addPayment(Number.parseFloat(response.order.due_amount.amount.toFixed(3)))
      activeInputPath.value = 'payments.0.amount'
    } catch (error) {
      toast.error((error as AxiosError<{
        message?: string
      }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
      close()
    } finally {
      loading.value = false
    }
  }

  onBeforeMount(async () => {
    await loadMeta()
  })

  const finalDueAmount = computed(() => {
    return (Math.round(meta.value.order?.due_amount.amount * 1000) / 1000) - (Math.round(currentTotalPaid.value * 1000) / 1000)
  })

  const totalCashPayment = computed(() => {
    return (form.state.payments
      .filter(p => p.method === 'cash')
      .reduce((total, p) => total + (Number(p.amount) || 0), 0))
  })

  watch(
    () => form.state.customer_given_amount,
    newVal => {
      const amountToBePaid = totalCashPayment.value
      const customerGivenAmount = Number.parseFloat(newVal)

      form.state.change_return
        = (Number.isNaN(customerGivenAmount) || Number.isNaN(amountToBePaid))
          || (customerGivenAmount < amountToBePaid)
          ? '0'
          : (customerGivenAmount - amountToBePaid).toFixed(3)
    },
  )

  const hasCashPayment = computed(() => totalCashPayment.value > 0)

  const currentTotalPaid = computed(() => {
    return (form.state.payments
      .reduce((total, p) => total + (Number(p.amount) || 0), 0))
  })

  watch(
    () => form.state.payments,
    () => {
      if (!hasCashPayment.value) {
        form.state.customer_given_amount = ''
      }
    },
    { deep: true },
  )

  function addPayment (defaultAmount: number | null = null) {
    form.state.payments.push({
      method: meta.value.paymentMethods.length > 0
        ? (meta.value.paymentMethods[0]?.id || null)
        : null,
      amount: defaultAmount,
      transaction_id: null,
      gift_card_code: null,
    })
  }

  function resolveActiveInputPath () {
    if (activeInputPath.value) {
      const paymentAmountPathMatch = activeInputPath.value.match(/^payments\.(\d+)\.amount$/)
      if (activeInputPath.value === 'customer_given_amount') {
        return activeInputPath.value
      }
      if (paymentAmountPathMatch) {
        const index = Number(paymentAmountPathMatch[1])
        if (form.state.payments[index]) {
          return activeInputPath.value
        }
      }
    }

    if (form.state.payments.length > 0) {
      return 'payments.0.amount'
    }

    return hasCashPayment.value ? 'customer_given_amount' : null
  }

  function getValueByPath (path: string): string {
    if (path === 'customer_given_amount') {
      return `${form.state.customer_given_amount || ''}`
    }

    const paymentAmountPathMatch = path.match(/^payments\.(\d+)\.amount$/)
    if (paymentAmountPathMatch) {
      const index = Number(paymentAmountPathMatch[1])
      return `${form.state.payments[index]?.amount ?? ''}`
    }

    return ''
  }

  function setValueByPath (path: string, value: string) {
    if (path === 'customer_given_amount') {
      form.state.customer_given_amount = value
      return
    }

    const paymentAmountPathMatch = path.match(/^payments\.(\d+)\.amount$/)
    if (paymentAmountPathMatch) {
      const index = Number(paymentAmountPathMatch[1])
      if (form.state.payments[index]) {
        form.state.payments[index].amount = value === '' ? null : value
      }
    }
  }

  function appendKeyToActiveInput (key: string) {
    const path = resolveActiveInputPath()
    if (!path || form.loading.value) {
      return
    }

    if ((key || '').toUpperCase() === 'C') {
      setValueByPath(path, '')
      return
    }

    const currentValue = getValueByPath(path)
    if (key === '.' && currentValue.includes('.')) {
      return
    }

    const baseValue = currentValue === '0' && key !== '.' ? '' : currentValue
    const nextValue = key === '.' && (baseValue === '' || baseValue == null)
      ? '0.'
      : `${baseValue}${key}`
    setValueByPath(path, nextValue)
  }

  function applyQuickPayToActiveInput (amount: number) {
    const path = resolveActiveInputPath()
    if (!path || form.loading.value) {
      return
    }

    const currentValue = Number.parseFloat(getValueByPath(path) || '0')
    const nextAmount = (Number.isNaN(currentValue) ? 0 : currentValue) + amount
    const precision = meta.value.order?.precision ?? 3
    setValueByPath(path, nextAmount.toFixed(precision))
  }

  function applyCalculatorResultToActiveInput (amount: number) {
    const path = resolveActiveInputPath()
    if (!path || form.loading.value) {
      return
    }

    const precision = meta.value.order?.precision ?? 3
    setValueByPath(path, amount.toFixed(precision))
  }

  const isSubmitDisabled = computed(() => {
    if (!form.loading.value && currentTotalPaid.value > 0) {
      if (hasCashPayment.value && Number.parseFloat(form.state.customer_given_amount || '0') < totalCashPayment.value) {
        return true
      }

      if (form.state.payment_mode == 'full' && finalDueAmount.value == 0) {
        return false
      } else if (form.state.payment_mode == 'partial' && finalDueAmount.value > 0) {
        return false
      }
    }
    return true
  })

  function resolvePrintType () {
    return form.state.payment_mode === 'full' ? 'invoice' : 'bill'
  }

  async function printAfterSubmit () {
    if (!props.qintrix.isSetup.value) {
      return
    }

    try {
      const type = resolvePrintType()
      const response = (await printPreview(
        props.orderId,
        type,
        undefined,
        props.registerId,
        'pdf',
      )).data.body

      if (response.printer?.qintrix_id && response.printer?.paper_size) {
        await props.qintrix.createJob(
          response.content,
          response.printer.qintrix_id,
          response.printer.paper_size,
          `${props.orderId}`,
          `order-payment-${type}`,
          'pdf',
        )
      }
    } catch (error) {
      toast.error((error as AxiosError<{
        message?: string
      }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
    }
  }

  async function submit (withPrint = false) {
    form.state.with_print = withPrint
    if (
      !form.loading.value
      && await form.submit(() => storePayment(props.orderId, form.state))
    ) {
      emit('payment-added')
      close()
      if (withPrint) {
        await printAfterSubmit()
      }
    } else {
      form.state.with_print = false
    }
  }
</script>

<template>
  <VDialog
    v-model="dialogModel"
    height="90vh"
    max-width="80%"
    persistent
  >
    <VCard class="payment-dialog-card">
      <div v-if="loading" class="d-flex h-100 justify-center align-center py-16">
        <VProgressCircular color="primary" indeterminate size="50" />
      </div>
      <VRow v-else class="payment-layout-row" no-gutters>
        <VCol class="finalize-col" cols="12" md="9">
          <FinalizePayment
            :active-input-path="activeInputPath"
            :final-due-amount="finalDueAmount"
            :form="form"
            :has-cash-payment="hasCashPayment"
            :meta="meta"
            :quick-pay-amounts="meta.quickPayAmounts.length > 0 ? meta.quickPayAmounts : defaultQuickPayAmounts"
            @add-payment="addPayment"
            @calculator-apply="applyCalculatorResultToActiveInput"
            @keypad-input="appendKeyToActiveInput"
            @quick-pay="applyQuickPayToActiveInput"
            @set-active-input="activeInputPath = $event"
          />
        </VCol>
        <VCol class="summary-col" cols="12" md="3">
          <OrderSummary
            :change-return="form.state.change_return"
            :current-total-paid="currentTotalPaid"
            :customer-given-amount="form.state.customer_given_amount"
            :final-due-amount="finalDueAmount"
            :form="form"
            :has-cash-payment="hasCashPayment"
            :is-submit-disabled="isSubmitDisabled"
            :order="meta.order"
            @close="close"
            @submit="submit"
          />
        </VCol>
      </VRow>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.payment-dialog-card {
  height: 80vh;
  overflow: hidden;
}

.payment-layout-row {
  height: 100%;
}

@media (min-width: 960px) {
  .finalize-col {
    height: 100%;
    overflow-y: auto;
  }

  .summary-col {
    height: 100%;
    position: sticky;
    top: 0;
    align-self: flex-start;
  }
}
</style>
