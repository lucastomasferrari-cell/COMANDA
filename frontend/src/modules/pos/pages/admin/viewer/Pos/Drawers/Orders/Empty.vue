<script lang="ts" setup>

  import { useI18n } from 'vue-i18n'

  defineProps<{ tab: string }>()
  const { t } = useI18n()
</script>

<template>
  <div class="empty-wrapper text-medium-emphasis">
    <VIcon :icon="tab==='active'?'tabler-clipboard-list':'tabler-calendar-event'" size="60" />
    <p class="title mb-1">{{ t(`pos::pos_viewer.${tab}_orders_empty_box.title`) }}</p>
    <p class="description">{{ t(`pos::pos_viewer.${tab}_orders_empty_box.description`) }}</p>
  </div>
</template>

<style lang="scss" scoped>
.empty-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  height: 50vh;

  .title {
    font-weight: bold;
  }

  .description {
    text-align: center;
  }
}
</style>
