<script lang="ts" setup>
  import { computed } from 'vue'

  const props = defineProps<{
    value: any
  }>()

  const rawJson = computed(() => JSON.stringify(props.value ?? {}, null, 2))
</script>

<template>
  <div class="json-viewer">
    <pre>{{ rawJson }}</pre>
  </div>
</template>

<style scoped>
</style>
