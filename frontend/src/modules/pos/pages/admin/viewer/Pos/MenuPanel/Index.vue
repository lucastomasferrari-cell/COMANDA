<script lang="ts" setup>
  import type { PlanoTable } from '@/modules/seatingPlan/components/SalonPlanoVisual.vue'
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { Category, PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { usePosViewerMode } from '@/modules/pos/composables/usePosViewerMode.ts'
  import Categories from './Categories/Index.vue'
  import Empty from './Empty.vue'
  import Products from './Products/Index.vue'
  import TablePlano from './TablePlano.vue'

  defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
    hasActiveOrder: boolean
  }>()

  defineEmits<{
    (e: 'pick-table-free', table: PlanoTable): void
    (e: 'pick-table-occupied', table: PlanoTable): void
  }>()

  const { t } = useI18n()
  const { mode } = usePosViewerMode()

  const activeCategories = ref<Category[]>([])
  const searchQuery = ref<string>('')
  const searchInputRef = ref<any>(null)

  const promptMessage = computed(() => mode.value === 'tables'
    ? t('pos::pos_viewer.menu_prompt.message_tables')
    : t('pos::pos_viewer.menu_prompt.message_quick'))

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
    :class="{'menu-items-container-center': !hasActiveOrder && mode !== 'tables' || meta.products.length === 0}"
  >
    <!-- Sin orden activa: en modo Mesas mostramos el plano read-only (click
         abre mesa). En modo Rapido, prompt simple con mensaje contextual. -->
    <TablePlano
      v-if="!hasActiveOrder && mode === 'tables'"
      :branch-id="form.branchId"
      @pick-free="(table: PlanoTable) => $emit('pick-table-free', table)"
      @pick-occupied="(table: PlanoTable) => $emit('pick-table-occupied', table)"
    />
    <div v-else-if="!hasActiveOrder" class="menu-prompt text-center">
      <div class="prompt-icon mb-3">
        <VIcon color="primary" icon="tabler-tools-kitchen-2" size="56" />
      </div>
      <h3 class="text-h6 mb-2">{{ t('pos::pos_viewer.menu_prompt.title') }}</h3>
      <p class="text-body-2 text-medium-emphasis">{{ promptMessage }}</p>
    </div>
    <div v-else-if="form.loadingMenuItems" class="loading">
      <VProgressCircular color="primary" indeterminate size="50" />
    </div>
    <template v-else>
      <template v-if="meta.products.length > 0">
        <div class="mb-2">
          <VTextField
            ref="searchInputRef"
            v-model="searchQuery"
            autofocus
            clearable
            :placeholder="t('pos::pos_viewer.search_products')"
            @keydown.esc="searchQuery = ''"
          >
            <template #prepend-inner>
              <VIcon icon="tabler-search" />
            </template>
          </VTextField>
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

.menu-prompt {
  max-width: 420px;
  padding: 1rem;
}

.prompt-icon {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  background: rgba(var(--v-theme-primary), 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

</style>
