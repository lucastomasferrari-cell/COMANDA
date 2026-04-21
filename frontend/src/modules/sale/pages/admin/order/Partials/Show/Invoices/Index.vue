<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import Invoice from './Invoice.vue'

  defineProps<{
    invoices: Record<string, any>[]
  }>()
  const { t } = useI18n()
</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('order::orders.show.cards.invoices') }}
    </VCardTitle>
    <VCardText class="pa-2">
      <Invoice v-for="invoice in invoices" :key="invoice.id" :invoice="invoice" />
    </VCardText>
  </VCard>
</template>
