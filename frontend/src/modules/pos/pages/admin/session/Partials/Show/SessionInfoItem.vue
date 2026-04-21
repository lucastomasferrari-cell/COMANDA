<script lang="ts" setup>
  interface Props {
    label: string
    value?: string | number | null
  }

  defineProps<Props>()
  const slots = useSlots()
</script>

<template>
  <div class="d-flex flex-column pa-3 session-info-item">
    <span class="font-weight-bold">{{ label }}</span>
    <div v-if="slots?.default" class="mt-1">
      <slot />
    </div>
    <span v-else class="text-subtitle-1 font-weight-medium mt-1">{{ value || "-" }}</span>
  </div>
</template>

<style lang="scss" scoped>
.session-info-item {
  width: 180px;
}
</style>
