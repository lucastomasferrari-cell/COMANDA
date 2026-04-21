<script lang="ts" setup>
defineProps<{ loading: boolean }>()
</script>

<template>
  <div v-if="loading" class="page-loader">
    <VProgressCircular
      color="primary"
      indeterminate
      size="38"
      width="3"
    />
  </div>
</template>
<style scoped>
.page-loader {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 150px;
}
</style>
