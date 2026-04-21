<script lang="ts" setup>
import { useI18n } from 'vue-i18n'
import { formatQty } from '@/modules/core/utils/support.ts'

defineProps<{
  lines: Record<string, any>[]
}>()

const { t } = useI18n()
</script>

<template>
  <div class="lines-container">
    <table class="invoice-table">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ t('invoice::invoices.show.description') }}</th>
          <th>{{ t('invoice::invoices.show.unit_price') }}</th>
          <th>{{ t('invoice::invoices.show.quantity') }}</th>
          <th>{{ t('invoice::invoices.show.sub_total') }}</th>
          <th>{{ t('invoice::invoices.show.total_tax') }}</th>
          <th>{{ t('invoice::invoices.show.total') }}</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(line, index) in lines" :key="line.id">
          <td class="text-center">{{ index + 1 }}</td>

          <td class="description">
            <div>{{ line.description }}</div>
            <small v-if="line.sku" class="sku">{{ line.sku }}</small>
          </td>

          <td class="text-right">
            <Money :money="line.unit_price" />
          </td>

          <td class="text-center">
            {{ formatQty(line.quantity) }}
          </td>

          <td class="text-right">
            <Money :money="line.line_total_excl_tax" />
          </td>

          <td class="text-right">
            <Money :money="line.tax_amount" />
          </td>

          <td class="text-right">
            <Money :money="line.line_total_incl_tax" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<style lang="scss" scoped>
.lines-container {
  overflow-x: auto;
  margin-top: 1.25rem;
}

.invoice-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 6px;

  thead {
    background: rgba(var(--v-theme-grey-100), 0.5);

    th {
      font-weight: 600;
      text-align: left;
      padding: 10px 12px;
      white-space: nowrap;
      border-bottom: 0.1px solid rgba(var(--v-theme-grey-300), 0.7);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    th:not(:first-child) {
      text-align: right;
    }
  }

  tbody {
    tr {
      background: rgb(var(--v-theme-surface));
      border-radius: 6px;

      &:nth-child(odd) {
        background: rgba(var(--v-theme-surface), 0.95);
      }

      td {
        padding: 10px 12px;
        vertical-align: middle;
        color: rgb(var(--v-theme-on-surface));

        &.text-center {
          text-align: center;
        }

        &.text-right {
          text-align: right;
          font-variant-numeric: tabular-nums;
        }
      }

      .description {
        max-width: 300px;
        line-height: 1.3;
      }

      .sku {
        display: block;
        font-size: 0.75rem;
        opacity: 0.7;
        color: rgb(var(--v-theme-on-surface));
      }
    }
  }
}
</style>
