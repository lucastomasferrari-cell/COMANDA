<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  defineProps<{ item: Record<string, any>, cellValue: boolean }>()
  const { t } = useI18n()
</script>

<template>
  <VChip v-if="cellValue" color="success" density="compact" size="small">
    <VIcon icon="tabler-check" size="14" start />
    {{ t('admin::admin.yes') }}
  </VChip>
  <VChip v-else color="default" density="compact" size="small">
    <VIcon icon="tabler-x" size="14" start />
    {{ t('admin::admin.no') }}
  </VChip>
</template>
