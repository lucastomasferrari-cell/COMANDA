<script lang="ts" setup>
  import type { PlanoTable } from '@/modules/seatingPlan/components/SalonPlanoVisual.vue'
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { Category, PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import Categories from './Categories/Index.vue'
  import Empty from './Empty.vue'
  import OpenItemDialog from './OpenItemDialog.vue'
  import Products from './Products/Index.vue'
  import TablePlano from './TablePlano.vue'

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
    hasActiveOrder: boolean
  }>()

  const emit = defineEmits<{
    (e: 'pick-table-free', table: PlanoTable): void
    (e: 'pick-table-occupied', table: PlanoTable): void
    (e: 'init-order', response: Record<string, any>): void
  }>()

  const { t } = useI18n()
  const { edit: refetchOrder } = useOrder()

  const activeCategories = ref<Category[]>([])
  const searchQuery = ref<string>('')
  const searchInputRef = ref<any>(null)
  const openItemDialog = ref<boolean>(false)

  const canAddOpenItem = computed(() =>
    props.form.mode === 'edit' && !!props.meta.order?.id,
  )

  // Al guardar un open item, el endpoint custom devuelve solo la orden
  // actualizada. Para refrescar el cart/form del viewer usamos el edit
  // endpoint (mismo payload que usa ActiveOrdersPanel al abrir una orden).
  const onOpenItemSaved = async () => {
    const orderId = props.meta.order?.id
    if (!orderId) return
    try {
      const response = (await refetchOrder(props.cart.cartId, orderId)).data.body
      emit('init-order', response)
    } catch {
      // fallback silencioso: el dialog ya mostró toast de éxito y el
      // item quedó persistido en DB; el user puede refrescar manual.
    }
  }

  const onChangeRootCategory = (category?: Category | null) => {
    activeCategories.value = category ? [category] : []
  }

  // Expuesto al padre para que el OrderPanel pueda delegar foco al search
  // con ESC, sin bajar una ref hasta adentro.
  defineExpose({
    focusSearch: () => searchInputRef.value?.focus?.(),
  })

</script>

<template>
  <div
    class="menu-items-container"
    :class="{'menu-items-container-center': meta.products.length === 0}"
  >
    <!-- Sin orden activa: el plano es el unico canal de arranque con mesa.
         Si el cajero quiere orden rapida, usa "+ Nueva" del panel
         izquierdo o del empty state del OrderPanel. -->
    <TablePlano
      v-if="!hasActiveOrder"
      :branch-id="form.branchId"
      @pick-free="(table: PlanoTable) => $emit('pick-table-free', table)"
      @pick-occupied="(table: PlanoTable) => $emit('pick-table-occupied', table)"
    />
    <div v-else-if="form.loadingMenuItems" class="loading">
      <VProgressCircular color="primary" indeterminate size="50" />
    </div>
    <template v-else>
      <template v-if="meta.products.length > 0">
        <div class="mb-2 d-flex ga-2 align-center">
          <VTextField
            ref="searchInputRef"
            v-model="searchQuery"
            autofocus
            class="flex-grow-1"
            clearable
            hide-details
            :placeholder="t('pos::pos_viewer.search_products')"
            @keydown.esc="searchQuery = ''"
          >
            <template #prepend-inner>
              <VIcon icon="tabler-search" />
            </template>
          </VTextField>
          <VTooltip
            location="top"
            :text="canAddOpenItem
              ? t('pos::pos_viewer.open_item.tooltip')
              : t('pos::pos_viewer.open_item.needs_order')"
          >
            <template #activator="{ props: tooltipProps }">
              <div v-bind="tooltipProps">
                <VBtn
                  color="primary"
                  :disabled="!canAddOpenItem"
                  prepend-icon="tabler-edit"
                  variant="tonal"
                  @click="openItemDialog = true"
                >
                  {{ t('pos::pos_viewer.open_item.button') }}
                </VBtn>
              </div>
            </template>
          </VTooltip>
        </div>
        <Categories
          :active-categories="activeCategories"
          :categories="meta.categories"
          @change-root="onChangeRootCategory"
        />
        <Products
          :active-categories="activeCategories"
          :cart="cart"
          :categories="meta.categories"
          :products="meta.products"
          :search-query="searchQuery"
        />
      </template>
      <Empty v-else />
    </template>
    <OpenItemDialog
      v-model="openItemDialog"
      :order-id="meta.order?.id ?? null"
      @saved="onOpenItemSaved"
    />
  </div>
</template>

<style lang="scss" scoped>
.menu-items-container {
  height: calc(83.6vh - var(--v-layout-navbar-height, 72px));
}

.menu-items-container-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  width: 100%;
}

</style>
