<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { Category, PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'
  import Categories from './Categories/Index.vue'
  import Empty from './Empty.vue'
  import Products from './Products/Index.vue'

  defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
  }>()

  const { t } = useI18n()

  const activeCategories = ref<Category[]>([])
  const searchQuery = ref<string>('')

  const onChangeRootCategory = (category?: Category | null) => {
    activeCategories.value = category ? [category] : []
  }

</script>

<template>
  <div class="menu-items-container" :class="{'menu-items-container-center':meta.products.length === 0}">
    <div v-if="form.loadingMenuItems" class="loading">
      <VProgressCircular color="primary" indeterminate size="50" />
    </div>
    <template v-else>
      <template v-if="meta.products.length > 0">
        <div class="mb-2">
          <VTextField
            v-model="searchQuery"
            clearable
            :placeholder="t('pos::pos_viewer.search_products')"
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

</style>
