<script lang="ts" setup>
  defineProps<{ item: any }>()
</script>

<template>
  <VTooltip location="top">
    <template #activator="{ props }">
      <VChip
        color="info"
        size="small"
        v-bind="props"
      >
        {{ item.log_name }}
      </VChip>
    </template>
    <span>{{ item.description }}</span>
  </VTooltip>
</template>
