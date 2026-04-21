<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import Order from '../../../Orders/Order.vue'
  import OrderBulkActions from './OrderBulkActions.vue'

  const props = defineProps<{
    table: Record<string, any>
    cartId: string
    branchId: number | null
    qintrix: ReturnType<typeof useQintrix>
  }>()

  defineEmits<{
    (e: 'refresh'): void
    (e: 'store-payment' | 'refund-order' | 'cancel-order' | 'view-order' | 'print-order', value: string | number): void
    (e: 'init-order', response: Record<string, any>): void
  }>()

  const { t } = useI18n()

  const orders = computed<Record<string, any>[]>(() => props.table.order ? [props.table.order] : props.table.orders)
  const isBillingMerged = computed<boolean>(() => (props.table.current_merge && orders.value.length > 0 && props.table.current_merge.type.id == 'billing') || false)

</script>

<template>
  <div class="pb-3 mt-2">
    <div class="d-flex mb-1 align-center justify-space-between font-weight-bold text-h6">
      <div class="d-flex align-center gap-1">
        <VIcon color="info" icon="tabler-salad" size="17" />
        {{ t(`seatingplan::tables.${isBillingMerged ? 'orders_details' : 'order_details'}`) }}
      </div>
    </div>
    <div class="px-3">
      <Order
        v-for="order in orders"
        :key="order.id"
        :branch-id="branchId"
        :cart-id="cartId"
        :is-billing-merged="isBillingMerged"
        is-table-viewer
        :order="order"
        :qintrix="qintrix"
        @cancel-order="(orderId:number|string)=>$emit('cancel-order',orderId)"
        @init-order="(response:Record<string,any>)=>$emit('init-order',response)"
        @print-order="(orderId:number|string)=>$emit('print-order',orderId)"
        @refresh="$emit('refresh')"
        @refund-order="(orderId:number|string)=>$emit('refund-order',orderId)"
        @store-payment="(orderId:number|string)=>$emit('store-payment',orderId)"
        @view-order="(orderId:number|string)=>$emit('view-order',orderId)"
      />
      <OrderBulkActions
        v-if="isBillingMerged"
        :merge-id="table.current_merge?.id"
        :orders="orders"
        @store-payment="(orderId:number|string)=>$emit('store-payment',orderId)"
      />
    </div>
  </div>
</template>
