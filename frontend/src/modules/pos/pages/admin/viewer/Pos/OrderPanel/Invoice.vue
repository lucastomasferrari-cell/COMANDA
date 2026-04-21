<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import { useI18n } from 'vue-i18n'
  import { formatPrice } from '@/modules/core/utils/formatters.ts'

  const props = defineProps<{
    cart: UseCart
    isEditMode: boolean
    amountDue: number
  }>()
  const { data, removeDiscount } = props.cart
  const { t } = useI18n()
</script>

<template>
  <div class="invoice-card">
    <div class="row-line">
      <span>{{ t('pos::pos_viewer.subtotal') }}</span>
      <span>{{ data.subTotal.formatted }}</span>
    </div>
    <div v-if="data.discount?.id" class="row-line discount-row ">
      <span class="discount-title">
        {{ t('pos::pos_viewer.discount') }}
        (<span class="discount-name">{{ data.discount?.name }}</span>)
        <VIcon
          class="cursor-pointer"
          color="error"
          icon="tabler-x"
          size="17"
          @click="removeDiscount"
        />
      </span>
      <span class="text-success">- {{ data.discount?.value?.formatted }}</span>
    </div>
    <div
      v-for="tax in data.taxes"
      :key="tax.id"
      class="row-line"
    >
      <span>{{ tax.name }}</span>
      <span>{{ tax.amount.formatted }}</span>
    </div>
    <div class="divider" />
    <div class="row-line total-row">
      <span>{{ t('pos::pos_viewer.total') }}</span>
      <span>{{ data.total.formatted }}</span>
    </div>
    <div v-if="isEditMode && amountDue>0" class="row-line amount_due-row">
      <span>{{ t('pos::pos_viewer.amount_due') }}</span>
      <span>{{ formatPrice(amountDue, data.total.currency || '', data.total.precision) }}</span>
    </div>

  </div>
</template>

<style lang="scss" scoped>
.invoice-card {
  padding: 10px 15px 15px 15px;
  border-radius: 8px;
  background: rgba(var(--v-theme-grey-200), 0.5);
  display: flex;
  flex-direction: column;

}

.row-line {
  display: flex;
  padding: 0.4rem 0;
  justify-content: space-between;
  font-size: 0.95rem;
  color: rgb(var(--v-theme-on-surface));
}

.row-line span:last-child {
  font-weight: 600;
}

.divider {
  height: 1px;
  margin: 6px 0;
  background: rgba(var(--v-theme-on-surface), 0.1);
}

.total-row {
  font-weight: 600;
  color: rgb(var(--v-theme-primary));
}

.amount_due-row {
  font-weight: bold;
  color: rgb(var(--v-theme-error));
}

.discount-title {
  display: flex;
  align-items: center;
}

.discount-name {
  text-decoration: underline;
  font-weight: bold;
}
</style>
