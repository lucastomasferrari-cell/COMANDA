<script lang="ts" setup>
import { useI18n } from 'vue-i18n'

defineProps<{
  invoice: Record<string, any>
}>()

const { t } = useI18n()
</script>

<template>
  <div class="invoice-summary-card">
    <div class="summary-row">
      <span class="label">
        {{ t('invoice::invoices.show.sub_total') }}
      </span>
      <span class="value">
        <Money :money="invoice.subtotal" />
      </span>
    </div>

    <div v-for="discount in invoice.discounts" :key="discount.id" class="discount summary-row">
      <span class="label">
        {{ t('invoice::invoices.show.discount') }} (<span class="text-decoration-underline">{{ discount.name }}</span>)
      </span>
      <span class="value">
        <Money :money="discount.amount" />
      </span>
    </div>

    <div v-for="tax in invoice.taxes" :key="tax.id" class="summary-row">
      <span class="label">
        {{ tax.name }}
      </span>
      <span class="value">
        <Money :money="tax.amount" />
      </span>
    </div>

    <hr>

    <div class="summary-row total">
      <span class="label">
        {{ t('invoice::invoices.show.total') }}
      </span>
      <span class="value">
        <Money :money="invoice.total" />
      </span>
    </div>
    <template v-if="invoice.invoice_kind.id !== 'credit_note'">
      <hr>
      <div class="summary-row">
        <span class="label">
          {{ t('invoice::invoices.show.paid_amount') }}
        </span>
        <span class="value">
          <Money :money="invoice.paid_amount" />
        </span>
      </div>
      <div class="summary-row">
        <span class="label">
          {{ t('invoice::invoices.show.refunded_amount') }}
        </span>
        <span class="value">
          <Money :money="invoice.refunded_amount" />
        </span>
      </div>
      <hr>
      <div class="summary-row net_paid">
        <span class="label">
          {{ t('invoice::invoices.show.net_paid') }}
        </span>
        <span class="value">
          <Money :money="invoice.net_paid" />
        </span>
      </div>
    </template>

  </div>
</template>

<style lang="scss" scoped>
.invoice-summary-card {
  background: rgb(var(--v-theme-grey-100), 0.6);
  padding: 16px 20px;
  border-radius: 8px;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;

  .summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .label {
      font-weight: 600;
    }

    .value {
      font-weight: 500;
    }

    &.total {
      margin-top: 2px;
      color: rgb(var(--v-theme-primary));
    }

    &.net_paid {
      margin-top: 2px;
      color: rgb(var(--v-theme-success));
    }

    &.discount {
      .value {
        color: rgb(var(--v-theme-success));
      }
    }
  }

  hr {
    border: 0;
    border-top: 1px dashed #ededed;
    margin: 4px 0 0;
  }
}
</style>
