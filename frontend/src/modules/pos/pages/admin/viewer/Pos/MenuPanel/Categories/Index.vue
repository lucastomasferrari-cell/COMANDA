<script lang="ts" setup>
  import type { Category } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'

  defineProps<{
    categories: Category[]
    activeCategories: Category[]
  }>()

  defineEmits(['change-root'])

  const { t } = useI18n()
</script>

<template>
  <div class="category-list">
    <div
      class="category-item"
      :class="{ active: activeCategories.length === 0 }"
      @click="$emit('change-root')"
    >
      <div class="category-name">
        {{ t('pos::pos_viewer.all_categories') }}
      </div>
    </div>
    <div
      v-for="category in categories"
      :key="category.id"
      class="category-item"
      :class="{ active: activeCategories.length>0 && activeCategories[0]?.id === category.id }"
      @click="$emit('change-root',category)"
    >
      <div class="category-name">
        {{ category.name }}
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.category-list {
  display: flex;
  overflow-x: auto;
  gap: 0.7rem;
  padding: 0.5rem 0;

  scrollbar-width: none;

  &::-webkit-scrollbar {
    display: none;
  }
}

.category-item {
  flex: 0 0 auto;
  background-color: rgb(var(--v-theme-grey-300));
  border-radius: 15px;
  padding: 8px 18px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  text-align: center;
  cursor: pointer;

  .category-name {
    font-weight: 600;
  }

  &.active {
    background-color: rgb(var(--v-theme-primary));

    .category-name {
      color: rgb(var(--v-theme-on-primary));
    }
  }
}
</style>
