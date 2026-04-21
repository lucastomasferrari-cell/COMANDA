<script lang="ts" setup>

  import { useI18n } from 'vue-i18n'

  defineProps<{
    categories: Record<string, any>[]
    activeCategory?: Record<string, any> | null
    level: number
  }>()
  defineEmits(['change'])
  const { t } = useI18n()
</script>

<template>
  <div class="category-sub-list">
    <div
      class="category-item"
      :class="{ active: !activeCategory }"
      @click="$emit('change',null,level)"
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
      :class="{ active: activeCategory && activeCategory.id === category.id }"
      @click="$emit('change',category,level)"
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
</template>

<style lang="scss" scoped>
.category-sub-list {
  display: flex;
  overflow-x: auto;
  gap: 0.5rem;
  padding: 0.3rem 0;
  scrollbar-width: none;

  &::-webkit-scrollbar {
    display: none;
  }
}

.category-item {
  flex: 0 0 auto;
  background-color: rgb(var(--v-theme-surface));
  border-radius: 10px;
  padding: 6px;
  align-items: center;
  display: flex;
  flex-direction: row;
  width: 120px;
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
      width: 20px;
      height: 20px;
      object-fit: contain;
    }

    .icon {
      font-size: 20px;
      color: rgb(var(--v-theme-on-surface));
      transition: color 0.2s ease;
    }
  }

  .category-name {
    font-size: 12px;
    margin: 0 5px;
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
