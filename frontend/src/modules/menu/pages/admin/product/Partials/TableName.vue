<script lang="ts" setup>
  defineProps<{ item: any }>()

</script>

<template>
  <span class="text-h6 font-weight-bold">
    {{ item.name }}
  </span>
</template>
