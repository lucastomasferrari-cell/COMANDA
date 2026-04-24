<script lang="ts" setup>
  import { computed } from 'vue'
  import { useRoute } from 'vue-router'
  import PosHeader from '@/app/layouts/components/PosHeader.vue'

  // Sprint 3.A.bis — layout dedicado del POS, separado del AdminLayout.
  // El AdminLayout wrappea todo con DefaultLayoutWithVerticalNav que mete
  // el sidebar admin vertical de 260px + navbar de 64px + footer — todo
  // ruido que el cajero no necesita y que le come 350px de viewport
  // útil en una tablet. Este layout en cambio:
  //   - Header fijo 56px con logo chico + breadcrumb + acciones mínimas
  //   - Contenedor principal 100% del restante, sin padding interno
  //     (cada modo maneja su propio spacing)
  //   - El switcher vertical de modos vive DENTRO del PosViewer, no del
  //     layout, porque solo aplica al POS viewer principal (no a
  //     kitchen_viewer ni customer_viewer que también usan este layout).

  const route = useRoute()

  // Key para forzar remount del RouterView al cambiar de ruta con params
  // distintos (mismo que hace AdminLayout). Evita que Vue reuse el
  // componente de la ruta anterior cuando solo cambian params.
  const routerViewKey = computed(() => {
    const params = Object.keys(route.params)
      .sort()
      .map(k => `${k}:${route.params[k]}`)
      .join('|')
    return `${String(route.name ?? '')}-${params}`
  })
</script>

<template>
  <div class="pos-layout">
    <PosHeader />
    <main class="pos-layout__main">
      <RouterView :key="routerViewKey" />
    </main>
  </div>
</template>

<style lang="scss" scoped>
.pos-layout {
  display: flex;
  flex-direction: column;
  height: 100vh;
  min-height: 0;
  background: rgb(var(--v-theme-background));
  overflow: hidden;
}

.pos-layout__main {
  flex: 1 1 auto;
  min-height: 0;
  overflow: hidden;
}
</style>
