<script lang="ts" setup>
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  defineProps<{ item: any, column: string }>()

  const { user } = useAuth()
</script>

<template>
  <VTooltip
    v-if="item[column] && item[column]['original']"
    :disabled="user?.assigned_to_branch || item[column]['converted'].currency == item[column]['original'].currency"
    :text="item[column]['converted'].formatted"
  >
    <template #activator="{ props }">
      <span v-bind="props">{{ item[column]["original"].formatted }}</span>
    </template>
  </VTooltip>
  <span v-else-if="item[column]">{{ item[column].formatted }}</span>
</template>
