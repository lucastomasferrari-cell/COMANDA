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
</template>

<style lang="scss">
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
