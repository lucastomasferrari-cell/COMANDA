<script lang="ts" setup>
  import { computed } from 'vue'

  const props = withDefaults(defineProps<{
    minutes?: number | null | string
    precision?: number
    showUnit?: boolean
  }>(), {
    precision: 2,
    showUnit: true,
  })

  const totalMinutes = computed(() => {
    if (props.minutes === null || props.minutes === undefined) return null
    const parsed = typeof props.minutes === 'string' ? Number(props.minutes) : props.minutes
    if (Number.isNaN(parsed)) return null
    return Math.max(0, Math.floor(parsed))
  })

  const label = computed(() => {
    if (!totalMinutes.value) return '0h'
    const hours = Math.floor(totalMinutes.value / 60)
    const minutes = totalMinutes.value % 60
    const formatted = `${hours}:${String(minutes).padStart(2, '0')}`
    return props.showUnit ? `${formatted}h` : formatted
  })

  const tooltipText = computed(() => {
    if (!totalMinutes.value) return null
    return `${totalMinutes.value}m`
  })

</script>

<template>
  <VTooltip v-if="tooltipText" location="top" :text="tooltipText">
    <template #activator="{ props: tooltipProps }">
      <span v-bind="tooltipProps">{{ label }}</span>
    </template>
  </VTooltip>
  <span v-else>{{ label }}</span>
</template>
