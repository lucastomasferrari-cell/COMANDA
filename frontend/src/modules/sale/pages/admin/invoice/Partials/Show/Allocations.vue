<script lang="ts" setup>
import { useI18n } from 'vue-i18n'

interface MoneyFormat {
  amount: number
  formatted: string
  currency: string
  precision: number
}

interface PaymentMethod {
  id: string
  name: string
  icon: string
  color: string
}

interface PaymentType {
  id: string
  name: string
}

interface Payment {
  id: number
  method: PaymentMethod
  type: PaymentType
  transaction_id: string | null
}

interface Allocation {
  id: number
  currency: string
  currency_rate: string
  amount: {
    original: MoneyFormat
    converted: MoneyFormat
  }
  payment: Payment
}

defineProps<{
  allocations: Allocation[]
}>()

const { t } = useI18n()
</script>

<template>
  <div class="payment_summary-card">
    <p class="text-subtitle-1 font-weight-bold border-b-dashed mb-2 pb-2">
      {{ t('invoice::invoices.show.payment_summary') }}
    </p>

    <div v-for="allocation in allocations" :key="allocation.id" class="d-flex align-center justify-space-between py-2">
      <div class="d-flex align-center gap-2">
        <VIcon class="mr-2" :color="allocation.payment.method?.color" :icon="allocation.payment.method?.icon"
          size="24" />

        <div>
          <div class="font-weight-bold ">
            {{ allocation.payment.method?.name }}
            <span v-if="allocation.payment.type.id == 'refund'" class="text-error text-body-2">
              ({{ allocation.payment.type.name }})
            </span>
          </div>
          <div v-if="allocation.payment.transaction_id" class="text-body-2 ">
            {{ t('invoice::invoices.show.transaction_id') }} :
            {{ allocation.payment.transaction_id }}
          </div>
        </div>
      </div>

      <div class="text-right">
        <Money :money="allocation.amount" />
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.payment_summary-card {
  background: rgb(var(--v-theme-grey-100), 0.6);
  padding: 16px 20px;
  border-radius: 8px;
}

.border-b-dashed {
  border-bottom: 1px dashed rgba(0, 0, 0, 0.15);
}
</style>
