<script lang="ts" setup>
  import type { Category } from '@/modules/pos/contracts/posViewer.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    categories: Category[]
    activeCategories: Category[]
  }>()

  defineEmits(['change-root'])

  const { t } = useI18n()

  // Orden: display_order si existe el campo, sino alfabetico por nombre.
  // El Category contract no lo tipa pero el backend lo puede mandar —
  // lo leemos como cast tolerante.
  const sortedCategories = computed(() => {
    const hasOrder = props.categories.some(c => typeof (c as any).display_order === 'number')
    return [...props.categories].sort((a, b) => {
      if (hasOrder) {
        const ao = (a as any).display_order ?? 999
        const bo = (b as any).display_order ?? 999
        if (ao !== bo) return ao - bo
      }
      return (a.name ?? '').localeCompare(b.name ?? '', 'es', { sensitivity: 'base' })
    })
  })

  // Color de la categoria: viene del backend como campo `color` (hex).
  // Fallback a gris neutro si no esta seteado.
  const colorOf = (category: Category): string => {
    const c = (category as any).color
    return typeof c === 'string' && /^#[0-9a-f]{6}$/i.test(c) ? c : '#B0B0B0'
  }
</script>

<template>
  <!-- Si no hay categorias con productos, no mostrar la barra de tabs.
       El grid abajo muestra el empty state correspondiente. -->
  <div v-if="sortedCategories.length > 0" class="category-list">
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
      v-for="category in sortedCategories"
      :key="category.id"
      class="category-item"
      :class="{ active: activeCategories.length > 0 && activeCategories[0]?.id === category.id }"
      :style="activeCategories[0]?.id === category.id ? { '--category-accent': colorOf(category) } : undefined"
      @click="$emit('change-root', category)"
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
  background-color: rgb(var(--v-theme-surface-variant));
  border: 1px solid transparent;
  border-radius: 15px;
  padding: 10px 20px;
  min-height: 48px; /* touch target Sprint 1.B BENCHMARK_POS #6 */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  text-align: center;
  cursor: pointer;
  transition: background-color 150ms ease, border-color 150ms ease;

  &:hover {
    border-color: rgba(var(--v-theme-on-surface), 0.12);
  }

  .category-name {
    font-size: 1rem; /* 16px — Sprint 1.B BENCHMARK #5 */
    font-weight: 600;
  }

  /* Tab activo: usa el color de la categoria si esta definido (C.5);
     fallback al color primary del theme. */
  &.active {
    background-color: var(--category-accent, rgb(var(--v-theme-primary)));

    .category-name {
      color: rgb(var(--v-theme-on-primary));
    }
  }
}
</style>
