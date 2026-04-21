<script lang="ts" setup>
  import { ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { formatPrice } from '@/modules/core/utils/formatters.ts'
  import CalculatorDialog from './CalculatorDialog.vue'

  defineProps<{
    form: Record<string, any>
    meta: Record<string, any>
    hasCashPayment: boolean
    quickPayAmounts: number[]
    activeInputPath: string | null
  }>()

  defineEmits<{
    (e: 'set-active-input', path: string): void
    (e: 'keypad-input', key: string): void
    (e: 'quick-pay', amount: number): void
    (e: 'calculator-apply', amount: number): void
  }>()

  const { t } = useI18n()
  const keypadButtons = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '.', '0', 'C']
  const openCalculator = ref(false)
</script>

<template>
  <VRow class="mt-4" dense>
    <VCol cols="12" md="5">
      <h4 class="section-title">{{ t('order::orders.numeric_keypad') }}</h4>
      <div class="grid keypad-grid">
        <VBtn
          v-for="key in keypadButtons"
          :key="key"
          class="pad-btn"
          :class="{ 'pad-btn-clear': key.toUpperCase() === 'C' }"
          :color="key === 'C' ? 'error' : 'default'"
          :variant="key === 'C' ? 'tonal' : 'outlined'"
          @click="$emit('keypad-input', key)"
        >
          {{ key }}
        </VBtn>
      </div>
      <VBtn
        class="calculator-open-btn mt-3"
        color="primary"
        variant="tonal"
        @click="openCalculator = true"
      >
        <VIcon icon="tabler-calculator" start />
        {{ t('order::orders.calculator') }}
      </VBtn>
    </VCol>
    <VCol cols="12" md="6" offset="1">
      <h4 class="section-title">{{ t('order::orders.quick_pay') }}</h4>
      <div class="grid quick-pay-grid  pb-4" :class="{'border-b-dashed':hasCashPayment}">
        <VBtn
          v-for="amount in quickPayAmounts"
          :key="amount"
          class="quick-pay-btn"
          color="warning"
          variant="tonal"
          @click="$emit('quick-pay', amount)"
        >
          +{{ formatPrice(amount, meta.order?.currency, meta.order?.precision) }}
        </VBtn>
      </div>

      <VRow>
        <VCol cols="12">
          <VTextField
            v-if="hasCashPayment"
            v-model="form.state.customer_given_amount"
            v-decimal-en
            class="mt-4"
            :error="!!form.errors.value?.customer_given_amount"
            :error-messages="form.errors.value?.customer_given_amount"
            :label="t('order::orders.customer_given_amount')"
            :prefix="meta.order?.currency"
            :readonly="form.loading.value"
            @focus="$emit('set-active-input', 'customer_given_amount')"
          />
        </VCol>
        <VCol cols="12">
          <VTextField
            v-if="hasCashPayment"
            v-model="form.state.change_return"
            color="success"
            :label="t('order::orders.change_return')"
            :prefix="meta.order?.currency"
            readonly
          />
        </VCol>
      </VRow>
    </VCol>
  </VRow>
  <CalculatorDialog
    v-model="openCalculator"
    @apply="$emit('calculator-apply', $event)"
  />
</template>

<style lang="scss" scoped>
.section-title {
  color: rgb(var(--v-theme-grey-700));
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 1rem;
}

.grid {
  display: grid;
  gap: 0.75rem;
}

.keypad-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.quick-pay-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.pad-btn,
.quick-pay-btn {
  height: 68px;
  font-size: 1.4rem;
  font-weight: 700;
  text-transform: none;
}

.pad-btn {
  border: 1px dashed rgba(var(--v-theme-grey-300), 0.9);
}

.pad-btn-clear {
  border: none;
}

.quick-pay-btn {
  font-size: 1.25rem;
}

.calculator-open-btn {
  width: 100%;
  min-height: 52px;
  border-radius: 12px;
  font-weight: 700;
  text-transform: none;
}
</style>
