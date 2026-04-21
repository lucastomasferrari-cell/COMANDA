<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    invoice: Record<string, any>
  }>()
  const { t } = useI18n()
</script>

<template>
  <VRow>
    <VCol cols="12" md="6">
      <span class="text-subtitle-1 font-weight-bold">{{ t('invoice::invoices.show.uuid') }}: </span>
      <span class="text-caption text-medium-emphasis">{{ invoice.uuid }}</span>
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>

</style>
