<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'
  import NavbarAction from '@/app/layouts/components/NavbarAction.vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
  }>()
  const { t } = useI18n()
  const { user } = useAuth()
  const router = useRouter()

  const { form } = toRefs(props)

  function goToCustomerViewer () {
    const route = router.resolve({
      name: 'admin.pos.customer_viewer',
      params: { cartId: props.cart.cartId },
    })

    window.open(route.href, '_blank')
  }

</script>

<template>

  <teleport to="#main-header-left-content">
    <div class="pos-header-selects d-flex align-center ga-3">
      <VSelect
        v-if="!user?.assigned_to_branch"
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
      <VSelect
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
      <VTooltip>
        <template #activator="{ props }">
          <NavbarAction v-bind="props">
            <VBtn
              color="default"
              icon="tabler-device-tv"
              variant="text"
              @click="goToCustomerViewer"
            />
          </NavbarAction>
        </template>
        <span>
          {{ t('pos::pos_viewer.customer_viewer') }}
        </span>
      </VTooltip>
    </div>

  </teleport>
</template>

<style lang="scss" scoped>
.pos-header-selects {
  padding: 8px 12px;
}

.pos-select {
  width: 190px;
  max-width: 200px;
}

.v-input--density-compact .v-field__input {
  font-size: 14px;
}
</style>
