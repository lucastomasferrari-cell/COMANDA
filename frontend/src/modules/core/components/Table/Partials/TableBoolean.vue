<script lang="ts" setup>

  import { useI18n } from 'vue-i18n'

  defineProps<{ item: any, column: string }>()
  const { t } = useI18n()
</script>

<template>
  <VChip
    class="text-white"
    :color="item[column] ? 'success' : 'error'"
    size="small"
  >
    <VIcon
      :icon="item[column] ? 'tabler-check' : 'tabler-x'"
      size="16"
      start
    />
    {{ t(`admin::admin.table.${(item[column] ? 'yes' : 'no')}`) }}
  </VChip>
</template>
