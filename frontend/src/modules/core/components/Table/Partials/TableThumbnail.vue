<script lang="ts" setup>
  defineProps<{ item: any, column: string }>()

</script>

<template>
  <div class="thumbnail-holder">
    <img v-if="item[column]" alt="thumbnail" :src="item[column].preview_image_url">
    <VIcon v-else icon="tabler-photo-filled" size="50" />
  </div>
</template>

<style lang="scss" scoped>
.thumbnail-holder {
  position: relative;
  border: 1px dashed #e2e8f0;
  background: transparent;
  height: 50px;
  width: 50px;
  border-radius: 6px;
  overflow: hidden !important;
  margin: 10px 0;

  > {
    i {
      position: absolute;

      color: #d9d9d9;
      top: 50%;
      left: 50%;
      -webkit-text-stroke: 0.06px #fff;
      transform: translate(-50%, -50%);
    }

    img {
      position: absolute;
      left: 50%;
      top: 50%;
      max-height: 100%;
      max-width: 100%;
      transform: translate(-50%, -50%);
    }
  }
}
</style>
