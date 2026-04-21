<script lang="ts" setup>

</script>

<template>
  <div
    class="layout-wrapper layout-blank"
    data-allow-mismatch
  >
    <RouterView/>
  </div>
</template>

<style scoped>
.layout-wrapper.layout-blank {
  flex-direction: column;
}
</style>
