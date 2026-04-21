<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import SubCategories from './SubCategories.vue'

  defineProps<{
    categories: Record<string, any>[]
    activeCategories: Record<string, any>[]
  }>()
  defineEmits(['change-root', 'change-sub'])

  const { t } = useI18n()
</script>

<template>
  <div class="mb-3">
    <div class="category-list">
      <div
        class="category-item"
        :class="{ active: activeCategories.length === 0 }"
        @click="$emit('change-root')"
      >
        <div class="category-media">
          <VIcon class="icon" icon="tabler-layout-grid" />
        </div>
        <div class="category-name">
          {{ t('pos::pos.all_categories') }}
        </div>
      </div>

      <div
        v-for="category in categories"
        :key="category.id"
        class="category-item"
        :class="{ active: activeCategories.length>0 && activeCategories[0]?.id === category.id }"
        @click="$emit('change-root',category)"
      >
        <div class="category-media">
          <img v-if="category.logo" alt="" :src="category.logo">
          <VIcon v-else class="icon" icon="tabler-photo-square-rounded" />
        </div>
        <div class="category-name">
          {{ category.name }}
        </div>
      </div>
    </div>
    <template v-for="(category,index) in activeCategories" :key="category.id">
      <SubCategories
        v-if="category.items.length > 0"
        :active-category="activeCategories[index+1]||null"
        :categories="category.items"
        :level="index+1"
        @change="(subCategory,level)=>$emit('change-sub',subCategory,level)"
      />
    </template>
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
  background-color: rgb(var(--v-theme-surface));
  border-radius: 12px;
  padding: 6px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 90px;
  height: 70px;
  text-align: center;
  transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
  }

  .category-media {
    display: flex;
    align-items: center;
    justify-content: center;

    img {
      width: 25px;
      height: 25px;
      object-fit: contain;
    }

    .icon {
      font-size: 25px;
      color: rgb(var(--v-theme-on-surface));
      transition: color 0.2s ease;
    }
  }

  .category-name {
    margin-top: 8px;
    font-size: 11px;
    font-weight: 500;
    color: rgb(var(--v-theme-on-surface));
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 70px;
    transition: color 0.2s ease;
  }

  &.active {
    background-color: rgb(var(--v-theme-primary));
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);

    .category-name {
      color: rgb(var(--v-theme-on-primary));
    }

    .icon {
      color: rgb(var(--v-theme-on-primary));
    }
  }
}
</style>
