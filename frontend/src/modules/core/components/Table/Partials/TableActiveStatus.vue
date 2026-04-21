<script lang="ts" setup>

  import { useI18n } from 'vue-i18n'

  defineProps<{ item: any }>()
  const { t } = useI18n()
</script>

<template>
  <VChip
    class="text-white"
    :color="item.is_active ? 'success' : 'error'"
    size="small"
  >
    <VIcon
      :icon="item.is_active ? 'tabler-check' : 'tabler-x'"
      size="16"
      start
    />
    {{ t(`admin::admin.table.${(item.is_active ? 'active' : 'inactive')}`) }}
  </VChip>
</template>
