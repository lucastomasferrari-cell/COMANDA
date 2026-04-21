<script lang="ts" setup>
  defineProps<{ table: Record<string, any> }>()
  defineEmits<{ (e: 'show-details', tableId: number): void }>()
</script>

<template>
  <VCard @click="$emit('show-details', table.id)">
    <VCardText class="pa-4">
      <div class="d-flex justify-space-between align-center">
        <div class="text-h6 font-weight-bold">{{ table.name }}</div>
        <VChip
          :color="table.status.color"
          size="small"
          text-color="white"
        >
          {{ table.status.name }}
        </VChip>
      </div>
      <div class="mt-2 text-caption text-grey">
        {{ table.zone?.name }} • {{ table.floor?.name }}
      </div>
    </VCardText>
  </VCard>
</template>
