<script lang="ts" setup>
  import type { PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { usePosViewerMode } from '@/modules/pos/composables/usePosViewerMode.ts'

  const props = defineProps<{
    meta: PosMeta
    isNarrow?: boolean
  }>()

  defineEmits<{
    (e: 'on-click-action', value: string): void
    (e: 'open-active-orders'): void
  }>()

  const { t } = useI18n()
  const { can } = useAuth()
  const { mode, setMode } = usePosViewerMode()

  const hasDineIn = computed(() => props.meta.orderTypes?.some(type => type.id === 'dine_in'))

  // Las acciones globales del viewer. "search_order" abre el mismo drawer
  // que "orders" pero el caller decide focusear el buscador.
  const actions = computed(() => [
    {
      id: 'table_viewer',
      icon: 'tabler-brand-airtable',
      label: t('pos::pos_viewer.actions.table_viewer'),
      visible: hasDineIn.value && can('admin.tables.viewer') && mode.value === 'tables',
    },
    {
      id: 'manage_cash_movement',
      icon: 'tabler-cash',
      label: t('pos::pos_viewer.actions.manage_cash_movement'),
      visible: can('admin.pos_cash_movements.create'),
    },
    {
      id: 'orders',
      icon: 'tabler-list-details',
      label: t('pos::pos_viewer.actions.orders'),
      visible: can('admin.orders.upcoming') || can('admin.orders.active'),
    },
    {
      id: 'search_order',
      icon: 'tabler-search',
      label: t('pos::pos_viewer.actions.search_order'),
      visible: can('admin.orders.upcoming') || can('admin.orders.active'),
    },
  ])
</script>

<template>
  <div class="top-actions-bar d-flex align-center ga-3 px-3 py-2">
    <!-- En md-and-down el ActiveOrdersPanel se colapsa a drawer; boton
         "☰ Comandas" lo abre. En lg+ el panel vive inline y no mostramos
         este boton. -->
    <VBtn
      v-if="isNarrow"
      icon="tabler-list"
      size="small"
      variant="text"
      @click="$emit('open-active-orders')"
    />
    <!-- Segmented control: 2 botones iguales, el activo con fondo primary.
         Usamos div + clicks en vez de VBtnToggle porque el toggle de Vuetify
         corta el texto cuando density='compact' y el styling custom es mas
         predecible. -->
    <div class="mode-segmented" role="tablist" :aria-label="t('pos::pos_viewer.mode.tables')">
      <button
        class="segment"
        :class="{ active: mode === 'tables' }"
        role="tab"
        :aria-selected="mode === 'tables'"
        type="button"
        @click="setMode('tables')"
      >
        <VIcon icon="tabler-brand-airtable" size="18" />
        <span class="label">{{ t('pos::pos_viewer.mode.tables') }}</span>
      </button>
      <button
        class="segment"
        :class="{ active: mode === 'quick' }"
        role="tab"
        :aria-selected="mode === 'quick'"
        type="button"
        @click="setMode('quick')"
      >
        <VIcon icon="tabler-bolt" size="18" />
        <span class="label">{{ t('pos::pos_viewer.mode.quick') }}</span>
      </button>
    </div>

    <VSpacer />

    <template v-for="action in actions" :key="action.id">
      <VBtn
        v-if="action.visible"
        :prepend-icon="action.icon"
        size="small"
        variant="tonal"
        @click="$emit('on-click-action', action.id)"
      >
        {{ action.label }}
      </VBtn>
    </template>
  </div>
</template>

<style lang="scss" scoped>
.top-actions-bar {
  background: rgba(var(--v-theme-surface), 1);
  border-bottom: thin solid rgba(var(--v-theme-on-surface), 0.08);
  flex-shrink: 0;
}

.mode-segmented {
  display: inline-flex;
  flex-shrink: 0;
  background: rgba(var(--v-theme-on-surface), 0.06);
  border-radius: 10px;
  padding: 4px;
  gap: 4px;
}

.segment {
  all: unset;
  box-sizing: border-box;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  min-width: 118px;
  padding: 0.45rem 0.9rem;
  border-radius: 7px;
  font-size: 0.875rem;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.72);
  transition: color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
  white-space: nowrap;

  .label {
    line-height: 1;
  }

  &:hover:not(.active) {
    color: rgb(var(--v-theme-on-surface));
    background: rgba(var(--v-theme-on-surface), 0.04);
  }

  &.active {
    color: rgb(var(--v-theme-on-primary));
    background: rgb(var(--v-theme-primary));
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12);
  }

  &:focus-visible {
    outline: 2px solid rgba(var(--v-theme-primary), 0.6);
    outline-offset: 2px;
  }
}
</style>
