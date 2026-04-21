<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{ item: any }>()
  const { t } = useI18n()
</script>

<template>
  <VTooltip :text="t('order::orders.table.order_number')">
    <template #activator="{ props }">
      <span v-bind="props"> ({{ item.order_number }})</span>
    </template>
  </VTooltip>
  -
  <VTooltip :text="t('order::orders.table.reference_no')">
    <template #activator="{ props }">
      <span class=" text-primary" v-bind="props">{{ item.reference_no }}</span>
    </template>
  </VTooltip>
</template>
