<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { provide, ref } from 'vue'

  interface TabItem {
    label: string
    to: RouteLocationRaw
    icon?: string
  }

  defineProps<{
    tabs: TabItem[]
  }>()

  // Publicamos un flag + selector para que componentes anidados
  // (típicamente SmartDataTable → TableHeaderActions) puedan teleportar
  // su botón primario al row de tabs en vez de renderizarlo abajo.
  // Así quedan tabs y action alineados en un solo renglón.
  const actionsTargetId = 'page-tabs-actions-target'
  provide('pageTabsActionsTarget', ref(`#${actionsTargetId}`))
</script>

<template>
  <div>
    <div class="page-tabs-row d-flex align-center flex-wrap gap-2 mb-4">
      <VTabs align-tabs="start" class="flex-grow-1 page-tabs">
        <VTab
          v-for="tab in tabs"
          :key="typeof tab.to === 'string' ? tab.to : JSON.stringify(tab.to)"
          :to="tab.to"
          :prepend-icon="tab.icon"
        >
          {{ tab.label }}
        </VTab>
      </VTabs>
      <!-- Target para Teleport de acciones primarias del tab activo.
           SmartDataTable/TableHeaderActions detecta el provide y deposita
           su botón aquí — sino renderiza inline en su ubicación normal. -->
      <div :id="actionsTargetId" class="page-tabs-actions d-flex gap-2" />
    </div>
    <router-view />
  </div>
</template>

<style scoped>
.page-tabs {
  border-bottom: thin solid rgba(var(--v-theme-on-surface), 0.12);
}

.page-tabs-row {
  border-bottom: thin solid rgba(var(--v-theme-on-surface), 0.12);
}

.page-tabs-row .page-tabs {
  border-bottom: none;
}

.page-tabs-actions {
  padding-inline-end: 0.25rem;
  padding-block-end: 0.25rem;
  flex-shrink: 0;
}
</style>
