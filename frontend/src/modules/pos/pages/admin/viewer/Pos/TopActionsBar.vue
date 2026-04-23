<script lang="ts" setup>
  import type { PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  defineProps<{
    meta: PosMeta
    isNarrow?: boolean
  }>()

  defineEmits<{
    (e: 'on-click-action', value: string): void
    (e: 'open-active-orders'): void
  }>()

  const { t } = useI18n()
  const { can } = useAuth()

  // El toggle Mesas/Rapido se removio. El flujo es directo: click en
  // mesa del plano = orden con mesa; boton "+ Nueva" = orden rapida.
  // Action 'orders' REMOVIDO (Sprint 1.A): era redundante con
  // ActiveOrdersPanel permanente izquierdo — dos vistas del mismo dato
  // confundía al cajero. El componente Drawers/Orders/Index.vue queda
  // huérfano por si se lo quiere reactivar en una vista dedicada tipo
  // /admin/pos/comandas (historial completo con filtros).
  const actions = computed(() => [
    {
      id: 'manage_cash_movement',
      icon: 'tabler-cash',
      label: t('pos::pos_viewer.actions.manage_cash_movement'),
      visible: can('admin.pos_cash_movements.create'),
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
