<script lang="ts" setup>
  defineProps<{
    qrcode: string
  }>()
</script>

<template>
  <div class="qrcode-container">
    <VImg :src="'data:image/png;base64,'+qrcode" width="150" />
  </div>
</template>
<style lang="scss" scoped>
.qrcode-container {
  border: 2px dashed #ededed;
  padding: 0.6rem;
  border-radius: 0.7rem;
}
</style>
