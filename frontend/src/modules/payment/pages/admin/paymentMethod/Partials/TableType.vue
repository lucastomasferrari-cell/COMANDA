<script lang="ts" setup>
  defineProps<{ item: Record<string, any>, cellValue: Record<string, any> | string | null }>()
</script>

<template>
  <VChip v-if="typeof cellValue === 'object' && cellValue !== null" density="compact" size="small" :color="cellValue.color ? undefined : 'default'" :style="cellValue.color ? { backgroundColor: cellValue.color, color: '#fff' } : {}">
    <VIcon v-if="cellValue.icon" :icon="cellValue.icon" size="14" start />
    {{ cellValue.name }}
  </VChip>
  <span v-else>{{ cellValue }}</span>
</template>
