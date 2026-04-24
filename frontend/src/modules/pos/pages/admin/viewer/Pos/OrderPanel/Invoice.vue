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
/* Sprint 3.A commit 7 — Toast-style: subtotal/descuentos chicos en
   on-surface-variant, TOTAL grande en primary coral como el único
   elemento "gritador" del panel. Divider sutil separando.
   Sin background card (antes era grey-200 tintado, feo en dark) —
   ahora limpio contra el surface del panel. */
.invoice-card {
  padding: 8px 4px;
  display: flex;
  flex-direction: column;
}

.row-line {
  display: flex;
  padding: 4px 0;
  justify-content: space-between;
  align-items: baseline;
  font-size: 0.875rem;
  color: rgb(var(--v-theme-on-surface-variant));
}

.row-line span:last-child {
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface));
}

.divider {
  height: 1px;
  margin: 6px 0;
  background: rgba(var(--v-theme-on-surface), 0.12);
}

/* TOTAL: el número más grande del panel — text-h4 weight 700 coral
   primary. Es lo que el cajero ve a distancia cuando decide cobrar. */
.total-row {
  padding: 8px 0 4px 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.total-row span:last-child {
  font-size: 2rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
  letter-spacing: -0.01em;
}

.amount_due-row {
  font-weight: 600;
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
