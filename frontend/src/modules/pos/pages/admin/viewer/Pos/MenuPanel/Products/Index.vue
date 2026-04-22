<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { Category, Product } from '@/modules/pos/contracts/posViewer.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useDisplay } from 'vuetify'
  import ProductDialog from './Dialogs/ProductDialog.vue'
  import Item from './Item.vue'

  const { t } = useI18n()

  const props = defineProps<{
    products: Product[]
    searchQuery: string
    activeCategories: Category[]
    categories: Category[]
    cart: UseCart
  }>()

  // Mapa id -> color hex. Se usa en Item.vue para pintar el borde superior
  // del boton de producto con el color de su categoria primaria.
  const categoryColorMap = computed(() => {
    const map = new Map<number | string, string>()
    const walk = (cats: Category[]) => {
      for (const c of cats) {
        const color = (c as any).color
        if (typeof color === 'string' && /^#[0-9a-f]{6}$/i.test(color)) {
          map.set(c.id, color)
        }
        if (c.items?.length) walk(c.items)
      }
    }
    walk(props.categories)
    return map
  })

  const { xlAndUp, smAndDown } = useDisplay()
  const { searchQuery } = toRefs(props)
  const debouncedSearchQuery = refDebounced(searchQuery, 200)
  const productOptionDialog = ref<Record<string, any>>({
    open: false,
    product: null,
  })

  const categoryIds = computed(() => {
    const ids: Array<string | number> = []

    const setIds = (categories?: Category[]) => {
      if (!categories) return
      for (const category of categories) {
        if (!ids.includes(category.id)) {
          ids.push(category.id)
        }
        setIds(category.items)
      }
    }

    if (props.activeCategories.length > 0) {
      const lastCategory = props.activeCategories[props.activeCategories.length - 1] as Category
      ids.push(lastCategory.id)
      setIds(lastCategory.items)
    }

    return ids
  })

  const normalize = (s?: unknown): string =>
    (s?.toString() ?? '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/\p{Diacritic}/gu, '') // remove accents
      .replace(/\s+/g, ' ')
      .trim()

  const indexedProducts = computed(() =>
    props.products.map(product => ({
      product,
      normalizedName: normalize(product.name),
    })),
  )

  const matchesSearch = (normalizedName: string) => {
    const q = normalize(debouncedSearchQuery.value)
    if (!q) return true

    return q.split(' ').every(t => normalizedName.includes(t))
  }

  const isVisibleProduct = (product: Product, normalizedName: string) =>
    matchesSearch(normalizedName)
    && (categoryIds.value.length === 0 || (product.category_ids || []).some(catId => categoryIds.value.includes(catId)))

  const visibleProducts = computed(() =>
    indexedProducts.value
      .filter(({ product, normalizedName }) => isVisibleProduct(product, normalizedName))
      .map(({ product }) => product),
  )

  const productRows = computed(() => {
    const products = visibleProducts.value
    const columnsPerRow = xlAndUp.value ? 4 : (smAndDown.value ? 2 : 3)
    const rows = []
    for (let i = 0; i < products.length; i += columnsPerRow) {
      rows.push(products.slice(i, i + columnsPerRow))
    }
    return rows
  })

  const hasActiveSearch = computed(() => debouncedSearchQuery.value?.trim().length > 0)
  const hasActiveCategory = computed(() => props.activeCategories.length > 0)
  const showNoResults = computed(() => visibleProducts.value.length === 0 && hasActiveSearch.value)
  const showEmptyCategory = computed(() =>
    visibleProducts.value.length === 0 && hasActiveCategory.value && !hasActiveSearch.value,
  )

  const openProductOptionDialog = (product: Product) => {
    productOptionDialog.value = { open: true, product: product }
  }
</script>

<template>
  <div
    v-if="showNoResults"
    class="no-results-state d-flex flex-column align-center justify-center text-center py-8 px-4"
  >
    <VIcon class="mb-3" color="grey-500" icon="tabler-search-off" size="48" />
    <h4 class="mb-1 font-weight-medium">{{ t('pos::pos_viewer.search_empty.title') }}</h4>
    <p class="text-body-2 text-medium-emphasis mb-0">
      {{ t('pos::pos_viewer.search_empty.message') }}
    </p>
  </div>
  <div
    v-else-if="showEmptyCategory"
    class="no-results-state d-flex flex-column align-center justify-center text-center py-8 px-4"
  >
    <VIcon class="mb-3" color="grey-500" icon="tabler-basket" size="48" />
    <h4 class="mb-1 font-weight-medium">{{ t('pos::pos_viewer.category_empty.title') }}</h4>
    <p class="text-body-2 text-medium-emphasis mb-0">
      {{ t('pos::pos_viewer.category_empty.message') }}
    </p>
  </div>
  <VVirtualScroll
    v-else
    class="mt-1 pt-2 pl-2 pos-virtual-scroll"
    height="100%"
    :items="productRows"
  >
    <template #default="{ item: row, index }">
      <VRow :key="index" dense>
        <VCol
          v-for="product in row"
          :key="product.id"
          cols="12"
          md="4"
          sm="6"
          style="padding: 0"
          xlg="3"
        >
          <Item :cart="cart" :category-color-map="categoryColorMap" :product="product" @open-options-dialog="openProductOptionDialog" />
        </VCol>
      </VRow>
    </template>
  </VVirtualScroll>
  <ProductDialog
    v-if="productOptionDialog.open"
    v-model="productOptionDialog.open"
    :cart="cart"
    :product="productOptionDialog.product"
  />
</template>

<style lang="scss">
.pos-virtual-scroll.v-virtual-scroll {
  overflow-x: hidden !important;
}
</style>
