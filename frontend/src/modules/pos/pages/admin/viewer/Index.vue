<script lang="ts" setup>
  import { isUUID } from '@/modules/core/utils/support.ts'
  import { usePosViewer } from '@/modules/pos/composables/usePosViewer.ts'
  import ConfigurationErrors from '@/modules/pos/pages/admin/viewer/ConfigurationErrors/Index.vue'
  import Pos from '@/modules/pos/pages/admin/viewer/Pos/Index.vue'
  import TopHeader from './TopHeader.vue'

  const isNotFound = ref(false)
  const route = useRoute()
  const cartId = (route.params as Record<string, any>).cartId
  const {
    loadConfiguration,
    loadMenuItems,
    hasConfigurationErrors,
    hasActiveOrder,
    startNewOrder,
    form,
    meta,
    cart,
    initOrder,
    reset,
    qintrix,
  } = usePosViewer(cartId)

  onBeforeMount(async () => {
    if (isUUID(cartId)) {
      await loadConfiguration()
    } else {
      isNotFound.value = true
    }
  })
</script>

<template>
  <!-- Sprint 4 commit 9 — pos-viewer-root: flex column que va de
       PosLayout.__main hacia abajo. TopHeader (sub-header del viewer,
       sin teleport orphan) toma su altura natural; el resto baja al
       PageStateWrapper que con :deep() propaga height:100% para que
       Pos > .pos-viewer-layout pueda usar height:100% en lugar de
       calc(100vh - X). -->
  <div class="pos-viewer-root">
    <TopHeader
      v-if="(!form.loading && !isNotFound) || form.reloadBranchData"
      :cart="cart"
      :form="form"
      :meta="meta"
    />
    <PageStateWrapper :loading="form.loading" :not-found="isNotFound">
      <ConfigurationErrors
        v-if="hasConfigurationErrors"
        :form="form"
        :meta="meta"
        @on-opened-session="loadMenuItems"
      />
      <Pos
        v-else
        :cart="cart"
        :form="form"
        :has-active-order="hasActiveOrder"
        :meta="meta"
        :qintrix="qintrix"
        :start-new-order="startNewOrder"
        @init-order="initOrder"
        @reset="reset"
      />
    </PageStateWrapper>
  </div>
</template>

<style lang="scss" scoped>
/* Sprint 4 commit 9 — height chain scoped al viewer root. Sin esto,
   PageStateWrapper no propaga altura y .pos-viewer-layout (en Pos
   children) no puede usar height:100%. :deep() limita el blast
   radius — otras pantallas que usen PageStateWrapper no se afectan. */
.pos-viewer-root {
  height: 100%;
  min-height: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.pos-viewer-root > :deep(.page-state-wrapper) {
  flex: 1 1 auto;
  min-height: 0;
  display: flex;
  flex-direction: column;
}

/* PageStateWrapper renderea 3 wrappers v-if (loading / notFound /
   slot). El slot wrapper es el v-else <div> que envuelve el slot —
   tiene que estirarse para que su contenido (Pos > pos-viewer-layout)
   pueda usar height:100%. */
.pos-viewer-root > :deep(.page-state-wrapper) > div {
  flex: 1 1 auto;
  min-height: 0;
  display: flex;
  flex-direction: column;
}
</style>

<style lang="scss">
/* Estilos globales históricos del vendor (selectores .layout-* del
   AdminLayout viejo). Quedan no-op bajo PosLayout porque ese layout
   no expone .layout-wrapper / .layout-page-content. Mantenidos por
   si algún día se vuelve a admin-layout para esta ruta, en cuyo caso
   estos overrides retomarían efecto. */
.layout-wrapper.layout-nav-type-vertical.pos-layout .layout-navbar,
.layout-wrapper.layout-nav-type-vertical.pos-layout .layout-footer {
  padding-inline: 0 !important;
}

.layout-wrapper.layout-nav-type-vertical.pos-layout .layout-footer {
  display: none;
}

.layout-wrapper.layout-nav-type-vertical.pos-layout .layout-navbar .navbar-content-container {
  box-shadow: none !important;
}

.pos-layout .layout-page-content {
  padding-block: 0.5rem !important;
  padding-inline: 0.5rem !important;
}

.layout-wrapper.layout-nav-type-vertical.pos-layout:not(.layout-navbar-sticky) .layout-navbar .navbar-content-container {
  margin-block-start: 0 !important;
}
</style>
