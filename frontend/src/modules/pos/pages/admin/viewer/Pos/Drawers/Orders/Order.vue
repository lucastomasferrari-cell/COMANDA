<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { convertHexToRgba } from '@/modules/core/utils/color.ts'
  import OrderActions from './OrderActions.vue'

  withDefaults(defineProps<{
    order: Record<string, any>
    branchId: number | null
    cartId: string
    qintrix: ReturnType<typeof useQintrix>
    isTableViewer?: boolean
    isBillingMerged?: boolean
  }>(), {
    isBillingMerged: false,
    isTableViewer: false,
  })

  defineEmits<{
    (e: 'refresh'): void
    (e: 'store-payment' | 'refund-order' | 'cancel-order' | 'view-order' | 'print-order', value: string | number): void
    (e: 'init-order', response: Record<string, any>): void
  }>()

  const { t } = useI18n()

</script>

<template>
  <VCard class="mt-2" :class="{'border-b-dashed pb-2':isTableViewer}">
    <VCardText :class="{'pa-3':!isTableViewer,'pa-1':isTableViewer}">
      <div
        class="pb-3 ga-2 d-flex align-center justify-content-between w-100"
        :class="{'border-b-dashed':!isTableViewer}"
      >
        <div
          v-if="!isTableViewer"
          class="order-type d-flex align-center justify-center flex-column"
        >
          <div class="order-type-icon" :style="{backgroundColor: convertHexToRgba(order.type.color,0.1)}">
            <VIcon :color="order.type.color" :icon="order.type.icon" size="35" />
          </div>
          <span class="order-type-name">{{ order.type.name }}</span>
        </div>
        <div class="order-details" :style="[isTableViewer?'width:100%':'']">
          <div class="d-flex justify-space-between">
            <span class="date">{{ order.time }}</span>
            <div class="total-container">
              <template v-if="order.payment_status.id == 'partially_paid'">
                <span class="total">
                  {{ order.due_amount.formatted }}
                </span>
                <span class="total-slash"> / </span>
              </template>
              <span class="total">
                {{ order.total.formatted }}
              </span>
            </div>
          </div>
          <div class="d-flex mt-2 align-center justify-space-between">
            <span class="order-number">
              #{{ order.order_number }} -
              {{ order.customer_name }}
            </span>
            <div class="">
              <TableStatus :item="order" />&nbsp;
              <TableEnum column="payment_status" :item="order" />
            </div>
          </div>
          <div class="products">
            {{ t("pos::pos_viewer.count_of_items", {count: order.products.count}) }}:
            {{ order.products.names }}
          </div>
          <div v-if="order.scheduled_at" class="d-flex justify-end align-center">
            <span class="scheduled-at-value">{{ order.scheduled_at }}</span>
          </div>
        </div>
      </div>
      <OrderActions
        :branch-id="branchId"
        :cart-id="cartId"
        :is-billing-merged="isBillingMerged"
        :is-table-viewer="isTableViewer"
        :order="order"
        :qintrix="qintrix"
        @cancel-order="(orderId:number|string)=>$emit('cancel-order',orderId)"
        @init-order="(response:Record<string, any>)=>$emit('init-order',response)"
        @print-order="(orderId:number|string)=>$emit('print-order',orderId)"
        @refresh="$emit('refresh')"
        @refund-order="(orderId:number|string)=>$emit('refund-order',orderId)"
        @store-payment="(orderId:number|string)=>$emit('store-payment',orderId)"
        @view-order="(orderId:number|string)=>$emit('view-order',orderId)"
      />
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.date {
  color: rgba(var(--v-theme-on-surface), 0.4);
  font-size: 0.8rem;
}

.products {
  width: 50%;
  font-size: 0.7rem;
  color: rgba(var(--v-theme-on-surface), 0.4);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.4;
}

.total {
  font-weight: 700;
  font-size: 0.9rem;
}

.total-slash {
  color: rgba(var(--v-theme-on-surface), 0.5);
}

.order-number {
  font-size: 0.9rem;
  font-weight: bold;
}

.order-type {
  width: 15%;
}

.order-details {
  width: 85%;
}

.order-type-name {
  font-weight: 700;
  font-size: 0.7rem;
}

.order-type-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.scheduled-at-value {
  font-size: 0.85rem;
  font-weight: 700;
}
</style>
