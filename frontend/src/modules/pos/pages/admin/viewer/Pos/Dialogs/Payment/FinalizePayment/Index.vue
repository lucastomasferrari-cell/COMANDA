<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import NumericQuickPay from './NumericQuickPay.vue'
  import PaymentMode from './PaymentMode.vue'
  import Payments from './Payments.vue'

  defineProps<{
    meta: Record<string, any>
    form: Record<string, any>
    hasCashPayment: boolean
    finalDueAmount: number
    activeInputPath: string | null
    quickPayAmounts: number[]
  }>()

  defineEmits<{
    (e: 'add-payment'): void
    (e: 'set-active-input', path: string): void
    (e: 'keypad-input', key: string): void
    (e: 'quick-pay', amount: number): void
    (e: 'calculator-apply', amount: number): void
  }>()

  const { t } = useI18n()

</script>

<template>
  <div class="finalize-payment-wrapper">
    <VCardText>
      <h3 class="title">{{ t('order::orders.finalize_payment') }}</h3>
      <PaymentMode :form="form" :meta="meta" />
      <Payments
        :form="form"
        :meta="meta"
        @add-payment="$emit('add-payment')"
        @set-active-input="$emit('set-active-input', $event)"
      />
      <NumericQuickPay
        :active-input-path="activeInputPath"
        :form="form"
        :has-cash-payment="hasCashPayment"
        :meta="meta"
        :quick-pay-amounts="quickPayAmounts"
        @keypad-input="$emit('keypad-input', $event)"
        @quick-pay="$emit('quick-pay', $event)"
        @calculator-apply="$emit('calculator-apply', $event)"
        @set-active-input="$emit('set-active-input', $event)"
      />
    </VCardText>
  </div>
</template>

<style lang="scss" scoped>
.finalize-payment-wrapper {
  height: 100%;
}

.title {
  font-weight: bold;
  font-size: 1.6rem;
  margin-bottom: 2rem;
}

</style>
