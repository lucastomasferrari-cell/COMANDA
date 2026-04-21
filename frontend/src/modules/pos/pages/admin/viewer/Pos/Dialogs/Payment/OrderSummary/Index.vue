<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { formatPrice } from '@/modules/core/utils/formatters.ts'
  import RowLine from './RowLine.vue'

  const props = defineProps<{
    order: Record<string, any>
    customerGivenAmount: number | string
    changeReturn: number | string
    currentTotalPaid: number
    finalDueAmount: number
    hasCashPayment: boolean
    isSubmitDisabled: boolean
    form: Record<string, any>
  }>()

  defineEmits<{
    (e: 'close'): void
    (e: 'submit', value?: boolean): void
  }>()

  const { t } = useI18n()
  const { with_print } = toRefs(props.form.state)

</script>

<template>
  <div class="order-summary-wrapper">
    <VCardText class="order-summary-content">
      <h3 class="title">{{ t('order::orders.order_summary') }}</h3>
      <div class="mt-6">
        <RowLine :title="t('order::orders.total_products')" :value="order.total_products" />
        <RowLine :title="t('order::orders.sub_total')" :value="order.sub_total.formatted" />
        <RowLine
          v-if="order.discount"
          :title="t('order::orders.discount')"
          :value="order.discount?.formatted"
        />
        <RowLine :title="t('order::orders.total_tax')" :value="order.total_tax.formatted" />
        <RowLine
          grand-total
          :title="t('order::orders.grand_total')"
          :value="order.grand_total.formatted"
        />
        <RowLine :title="t('order::orders.total_paid')" :value="order.total_paid.formatted" />
        <RowLine
          :title="t('order::orders.current_total_paid')"
          :value="formatPrice(currentTotalPaid,order.currency,order.precision)"
        />
        <RowLine
          :title="t('order::orders.due_amount')"
          :value="formatPrice(finalDueAmount,order.currency,order.precision)"
        />
        <template v-if="hasCashPayment">
          <RowLine
            :title="t('order::orders.customer_given_amount')"
            :value="formatPrice(customerGivenAmount||'0',order.currency,order.precision)"
          />
          <RowLine
            change-return
            :title="t('order::orders.change_return')"
            :value="formatPrice(changeReturn||'0',order.currency,order.precision)"
          />
        </template>
      </div>
    </VCardText>
    <div class="card-footer">
      <div class="d-flex justify-space-between ga-2 w-100">
        <VBtn
          color="grey-300"
          :disabled="form.loading.value"
          size="large"
          style="width: 47%"
          @click="$emit('close')"
        >
          <VIcon icon="tabler-x" start />
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
        <VBtn
          color="secondary"
          :disabled="isSubmitDisabled"
          :loading="form.loading.value && !with_print"
          size="large"
          style="width: 47%"
          @click="$emit('submit')"
        >
          <VIcon icon="tabler-check" start />
          {{ t('admin::admin.buttons.submit') }}
        </VBtn>
      </div>
      <VBtn
        block
        class="mt-4"
        color="primary"
        :disabled="isSubmitDisabled"
        :loading="form.loading.value && with_print"
        size="large"
        @click="$emit('submit',true)"
      >
        <VIcon icon="tabler-printer" start />
        {{
          t(`order::orders.${form.state.payment_mode == 'full' ? 'submit_and_print_invoice' : 'submit_and_print_bill'}`)
        }}
      </VBtn>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.order-summary-wrapper {
  display: flex;
  flex-direction: column;
  height: 100%;
  background-color: rgb(var(--v-theme-grey-100));
}

.order-summary-content {
  flex: 1;
  overflow-y: auto;
}

.card-footer {
  padding: 1.5rem 0.8rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  border-top: 1px dashed rgba(var(--v-border-color), var(--v-border-opacity));
  background-color: rgb(var(--v-theme-grey-100));
}

.title {
  font-weight: bold;
  font-size: 1.1rem;
}

</style>
