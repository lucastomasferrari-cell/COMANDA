<script lang="ts" setup>
  // Stub — implementacion completa en commit siguiente (sub-bloque 2).
  // Solo renderiza un contenedor vacio para que el layout 3 columnas
  // funcione desde el commit 1.

  defineProps<{
    branchId: number | null
    cartId: string
  }>()
</script>

<template>
  <div class="active-orders-panel d-flex flex-column h-100">
    <div class="panel-header px-3 py-2">
      <h3 class="text-subtitle-1 font-weight-medium">Comandas activas</h3>
    </div>
    <div class="flex-grow-1 d-flex align-center justify-center text-medium-emphasis">
      <!-- placeholder: se reemplaza en commit 2 -->
      Cargando…
    </div>
  </div>
</template>

<style scoped>
.active-orders-panel {
  overflow: hidden;
}
.panel-header {
  border-bottom: thin solid rgba(var(--v-theme-on-surface), 0.1);
}
</style>
