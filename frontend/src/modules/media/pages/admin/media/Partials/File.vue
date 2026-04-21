<script lang="ts" setup>
  defineProps<{
    file: Record<string, any>
    selected: boolean
  }>()

</script>

<template>
  <div class="file-container" :class="{ selected }">
    <img alt="" :src="file.preview_image_url">
    <p class="filename">
      {{ file.name }}
    </p>
    <p v-if="file.is_file" class="human-size">
      {{ file.human_size }}
    </p>
  </div>
</template>

<style lang="scss" scoped>
.file-container {
  margin: 6px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  width: 146px;
  height: 146px;
  border: 1.5px dashed #d2d6de;
  border-radius: 6px;

  cursor: pointer;
}

img {
  width: 80px;
  height: 80px;
  object-fit: contain;
}

.filename {
  margin: 13px 0 1px;
  font-size: 0.7rem;
  width: 120px;
  text-align: center;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-weight: 700;
  letter-spacing: normal;
  line-height: 13px
}

.human-size {
  margin: 5px 0 0;
  font-size: 0.6rem;
  line-height: 10px
}

.selected {
  border-color: rgb(var(--v-theme-primary));
}
</style>
