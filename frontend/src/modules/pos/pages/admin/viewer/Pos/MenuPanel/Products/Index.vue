<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { Category, Product } from '@/modules/pos/contracts/posViewer.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
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

  // Mapa id -> color_hue (0-360) como fuente única de verdad visual.
  // El "color" hex legacy dejó de exponerse desde PosCategoryResource
  // (rompía por select parcial). Tile.vue deriva el hex con
  // hsl(hue 55% 50%) cuando necesita pintar el borde superior.
  const categoryHueMap = computed(() => {
    const map = new Map<number | string, number>()
    const walk = (cats: Category[]) => {
      for (const c of cats) {
        const hue = (c as any).color_hue
        if (typeof hue === 'number' && hue >= 0 && hue <= 360) {
          map.set(c.id, hue)
        }
        if (c.items?.length) walk(c.items)
      }
    }
    walk(props.categories)
    return map
  })

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

  // Grid auto-fill Sprint 1.B: antes se pre-armaban filas con
  // columnsPerRow fijo (xl=4, md=3, sm=2) para VVirtualScroll. Ahora el
  // layout es CSS Grid nativo con minmax(180px, 1fr) — se acomoda solo
  // al viewport (4-5 columnas en 1280px, 3 en 900px, etc.). Sin
  // VVirtualScroll: en el use case actual (hasta ~200 productos) el
  // rendering plano es más barato que la virtualización.

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
  <div v-else class="product-grid-scroll">
    <div class="product-grid">
      <Item
        v-for="product in visibleProducts"
        :key="product.id"
        :cart="cart"
        :category-hue-map="categoryHueMap"
        :product="product"
        @open-options-dialog="openProductOptionDialog"
      />
    </div>
  </div>
  <ProductDialog
    v-if="productOptionDialog.open"
    v-model="productOptionDialog.open"
    :cart="cart"
    :product="productOptionDialog.product"
  />
</template>

<style lang="scss" scoped>
/* Wrapper scrollable — toma la altura disponible del parent MenuPanel.
   Necesita min-height:0 para que el flex-grow del parent pueda encogerlo. */
.product-grid-scroll {
  height: 100%;
  min-height: 0;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 8px 8px 12px 8px;
}

/* Auto-fill con minmax(180,1fr): en 1280px de panel útil quedan 5-6 tiles
   por fila; en 900px quedan 4; en 600px (mobile) 3. Naturalmente responsive
   sin tener que observar el viewport. */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px;
  align-content: start;
}
</style>
