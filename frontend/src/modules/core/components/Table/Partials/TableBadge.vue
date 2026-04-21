<script lang="ts" setup>

  defineProps<{ item: any, itemKey: string, color?: string }>()
</script>

<template>
  <VChip
    :color="color||'primary'"
    size="small"
  >
    {{ item[itemKey] }}
  </VChip>
</template>
