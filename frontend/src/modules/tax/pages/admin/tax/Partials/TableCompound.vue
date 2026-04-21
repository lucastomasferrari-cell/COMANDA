<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{ item: any }>()
  const { t } = useI18n()
</script>

<template>
  <VChip
    class="text-white"
    :color="item.compound ? 'primary' : 'secondary'"
    size="small"
  >
    {{ t(`tax::taxes.table.${(item.compound ? 'compound' : 'uncompounded')}`) }}
  </VChip>
</template>
