<script lang="ts" setup>
  import type { PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { usePosViewerMode } from '@/modules/pos/composables/usePosViewerMode.ts'

  const props = defineProps<{
    meta: PosMeta
  }>()

  defineEmits<{
    (e: 'on-click-action', value: string): void
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
    <VBtnToggle
      :model-value="mode"
      color="primary"
      density="compact"
      mandatory
      variant="outlined"
      @update:model-value="(next) => setMode(next as 'tables' | 'quick')"
    >
      <VBtn value="tables">
        <VIcon icon="tabler-brand-airtable" start />
        {{ t('pos::pos_viewer.mode.tables') }}
      </VBtn>
      <VBtn value="quick">
        <VIcon icon="tabler-bolt" start />
        {{ t('pos::pos_viewer.mode.quick') }}
      </VBtn>
    </VBtnToggle>

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
</style>
