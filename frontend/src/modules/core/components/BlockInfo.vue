<script lang="ts" setup>
defineProps<{
  title: string
  value?: string | number
}>()
</script>

<template>
  <p class="mb-0 font-weight-bold">{{ title }}</p>
  <small>
    <slot>
      {{ value || "-" }}
    </slot>
  </small>
</template>
