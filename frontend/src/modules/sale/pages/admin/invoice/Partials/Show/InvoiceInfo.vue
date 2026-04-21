<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import Info from './Info.vue'

  defineProps<{
    invoice: Record<string, any>
  }>()

  const { t } = useI18n()

</script>

<template>
  <div class="border-b-dashed mt-2 pb-3">
    <VRow>
      <VCol cols="6" lg="3">
        <Info
          :label="t('invoice::invoices.show.invoice_number')"
          :value="invoice.invoice_number"
        />
      </VCol>
      <VCol cols="6" lg="3">
        <Info
          :label="t('invoice::invoices.show.invoice_date')"
          :value="`${invoice.issued_at.date} - ${invoice.issued_at.time}`"
        />
      </VCol>

      <VCol cols="6" lg="3">
        <Info :label="t('invoice::invoices.show.invoice_type')">
          <TableEnum column="type" :item="invoice" />
        </Info>
      </VCol>
      <VCol cols="6" lg="3">
        <Info :label="t('invoice::invoices.show.purpose')">
          <TableEnum column="purpose" :item="invoice" />
        </Info>
      </VCol>
    </VRow>
  </div>
</template>
