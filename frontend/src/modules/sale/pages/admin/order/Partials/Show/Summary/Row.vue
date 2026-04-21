<script lang="ts" setup>
  defineProps<{
    label: string
    value?: string | number
    border?: boolean
    primary?: boolean
  }>()
</script>

<template>
  <div
    class="d-flex justify-space-between align-center"
    :class="{'border-b-dashed py-2':border}"
  >
    <span class="font-weight-bold text-body-2 text-grey-darken-1" :class="{'text-primary':primary}">
      {{ label }}
    </span>
    <span class="text-body-1">
      <slot>
        {{ value || "-" }}
      </slot>
    </span>
  </div>
</template>
