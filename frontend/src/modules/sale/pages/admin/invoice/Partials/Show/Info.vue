<script lang="ts" setup>
  defineProps<{
    label: string
    value?: string | number
  }>()
</script>

<template>
  <div class="info">
    <div class="text-caption text-medium-emphasis">
      {{ label }}
    </div>
    <div class="mt-1 text-subtitle-2 font-weight-bold">
      <slot>
        {{ value || "-" }}
      </slot>
    </div>
  </div>
</template>

<style lang="scss" scoped>

</style>
