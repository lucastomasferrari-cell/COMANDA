<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { Category, Product } from '@/modules/pos/contracts/posViewer.ts'
  import { computed } from 'vue'
  import { useDisplay } from 'vuetify'
  import ProductDialog from './Dialogs/ProductDialog.vue'
  import Item from './Item.vue'

  const props = defineProps<{
    products: Product[]
    searchQuery: string
    activeCategories: Category[]
    cart: UseCart
  }>()

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

  const productRows = computed(() => {
    const products = indexedProducts.value
      .filter(({ product, normalizedName }) => isVisibleProduct(product, normalizedName))
      .map(({ product }) => product)
    const columnsPerRow = xlAndUp.value ? 4 : (smAndDown.value ? 2 : 3)
    const rows = []
    for (let i = 0; i < products.length; i += columnsPerRow) {
      rows.push(products.slice(i, i + columnsPerRow))
    }
    return rows
  })

  const openProductOptionDialog = (product: Product) => {
    productOptionDialog.value = { open: true, product: product }
  }
</script>

<template>
  <VVirtualScroll
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
          <Item :cart="cart" :product="product" @open-options-dialog="openProductOptionDialog" />
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
