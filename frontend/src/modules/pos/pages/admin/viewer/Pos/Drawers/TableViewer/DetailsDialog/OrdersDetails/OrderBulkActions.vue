<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { formatPrice } from '@/modules/core/utils/formatters.ts'

  const props = defineProps<{
    orders: Record<string, any>[]
    mergeId: number
  }>()

  defineEmits<{
    (e: 'store-payment', value: string | number): void
  }>()

  const { t } = useI18n()
  const { can } = useAuth()

  const total = computed(() => props.orders.reduce((sum, order) => sum + order.total.amount, 0))
  const firstOrder = computed(() => props.orders[0])
  const totalFormatted = computed(() => firstOrder.value ? formatPrice(total.value, firstOrder.value.total.currency, firstOrder.value.total.precision) : '')
  const dueAmount = computed(() => props.orders.reduce((sum, order) => sum + order.due_amount.amount, 0))
  const dueAmountFormatted = computed(() => firstOrder.value ? formatPrice(dueAmount.value, firstOrder.value.due_amount.currency, firstOrder.value.due_amount.precision) : '')
  const allowAddPayment = computed(() =>
    props.orders.length > 0
    && props.orders.filter(order => order.next_status.id === 'completed' && order.payment_status.id !== 'paid').length == props.orders.length,
  )
  const isPartiallyPaid = computed(() => props.orders.some(order => order.payment_status.id == 'partially_paid'))

</script>

<template>
  <div class="mt-4 d-flex align-center justify-space-between">
    <div class="d-flex align-center justify-space-between gap-4">
      <div>
        <span class="font-weight-bold">
          {{ t('pos::pos_viewer.total') }} :
        </span>
        {{ totalFormatted }}
      </div>
      <div>
        <span class="font-weight-bold">
          {{ t('pos::pos_viewer.due_amount') }} :
        </span>
        {{ dueAmountFormatted }}
      </div>
    </div>
    <VBtn
      v-if="can('admin.orders.receive_payment')"
      color="success"
      :disabled="!allowAddPayment || orders.length === 0"
      variant="tonal"
      @click="$emit('store-payment', orders[0]?.id ?? '')"
    >
      <VIcon icon="tabler-cash" start />
      {{ t(`pos::pos_viewer.order_actions.${isPartiallyPaid ? 'complete_payment' : 'pay_now'}`) }}
    </VBtn>
  </div>
</template>
