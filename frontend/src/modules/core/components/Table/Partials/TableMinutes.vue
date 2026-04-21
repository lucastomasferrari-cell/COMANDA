<script lang="ts" setup>
  import MinutesView from '@/modules/core/components/MinutesView.vue'

  defineProps<{ item: any, column: string }>()
</script>

<template>
  <MinutesView :minutes="item?.[column]" />
</template>
