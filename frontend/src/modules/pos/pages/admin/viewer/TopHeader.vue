<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { toRefs } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'

  // Sprint 4 commit 9 — el teleport to="#main-header-left-content"
  // estaba huérfano: ese target vivía en DefaultLayoutWithVerticalNav
  // (AdminLayout) pero los routes del POS migraron a PosLayout
  // en Sprint 3.A.bis. Sin target, Vue dejaba un mount point fantasma
  // y cada update reactivo (cart.processing, form.reloadBranchData,
  // meta.menus) crasheaba con __vnode null durante "component update".
  //
  // Fix: removemos el teleport. El contenido ahora se renderiza inline
  // como sub-header del viewer, dentro del flex column que arma
  // viewer/Index.vue. Este componente queda con altura natural
  // (~56px) y el split-screen abajo absorbe el resto via flex:1.
  //
  // El NavbarAction wrapper del botón customer-viewer se eliminó
  // porque ese componente le ponía un círculo border 1px (patrón del
  // navbar global) que no encaja en este sub-header. Reemplazado por
  // VBtn icon directo.

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
  }>()
  const { t } = useI18n()
  const router = useRouter()

  const { form } = toRefs(props)

  function goToCustomerViewer (): void {
    const route = router.resolve({
      name: 'admin.pos.customer_viewer',
      params: { cartId: props.cart.cartId },
    })
    window.open(route.href, '_blank')
  }
</script>

<template>
  <div class="pos-top-header d-flex align-center ga-3 px-3 py-2">
    <!-- Branch select histórico, hidden con v-if=false en el vendor.
         Mantenemos el bloque por si se reactiva en futuro multi-branch. -->
    <VSelect
      v-if="false"
      v-model="form.branchId"
      class="pos-select"
      density="compact"
      :disabled="form.reloadBranchData||cart.processing.value"
      item-title="name"
      item-value="id"
      :items="meta.branches"
      :loading="form.reloadBranchData"
      :placeholder="t('pos::pos_viewer.select_branch')"
      prepend-inner-icon="tabler-git-branch"
      variant="solo-filled"
    />

    <!-- Si hay un solo menu activo, no tiene sentido el selector.
         Se muestra el nombre como texto estatico para contexto. -->
    <VSelect
      v-if="meta.menus.length > 1"
      v-model="form.menuId"
      class="pos-select"
      density="compact"
      :disabled="!form.branchId||form.reloadBranchData||cart.processing.value"
      hide-details
      item-title="name"
      item-value="id"
      :items="meta.menus"
      :placeholder="t('pos::pos_viewer.select_menu')"
      prepend-inner-icon="tabler-list-details"
      variant="solo-filled"
    />
    <div v-else-if="meta.menus.length === 1" class="pos-menu-label d-flex align-center ga-2">
      <VIcon icon="tabler-list-details" size="18" />
      <span>{{ meta.menus[0].name }}</span>
    </div>

    <VSelect
      v-model="form.registerId"
      class="pos-select"
      density="compact"
      :disabled="!form.branchId||form.reloadBranchData||cart.processing.value"
      hide-details
      item-title="name"
      item-value="id"
      :items="meta.registers"
      :placeholder="t('pos::pos_viewer.select_register')"
      prepend-inner-icon="tabler-device-desktop-cog"
      variant="solo-filled"
    />

    <VSpacer />

    <VTooltip location="bottom">
      <template #activator="{ props: tipProps }">
        <VBtn
          v-bind="tipProps"
          color="default"
          icon="tabler-device-tv"
          size="small"
          variant="text"
          @click="goToCustomerViewer"
        />
      </template>
      <span>{{ t('pos::pos_viewer.customer_viewer') }}</span>
    </VTooltip>
  </div>
</template>

<style lang="scss" scoped>
.pos-top-header {
  flex-shrink: 0;
  background: rgb(var(--v-theme-surface));
  border-bottom: 1px solid rgb(var(--v-theme-border));
  min-height: 56px;
}

.pos-select {
  width: 190px;
  max-width: 200px;
}

/* v-field 16px en este sub-header (el cajero lo mira a distancia tablet). */
.v-input--density-compact .v-field__input {
  font-size: 1rem;
}

.pos-menu-label {
  color: rgba(var(--v-theme-on-surface), 0.7);
  font-size: 1rem;
}
</style>
