<script lang="ts" setup>
  import { computed } from 'vue'
  import { useDisplay } from 'vuetify'
  import Product from './Product.vue'

  const props = defineProps<{
    products: Record<string, any>[]
    activeCategories: Record<string, any>[]
  }>()

  const { smAndDown } = useDisplay()

  const categoryIds = computed(() => {
    const ids: number[] = []

    const setIds = (categories: Record<string, any>[]) => {
      for (const category of categories) {
        if (!ids.includes(category.id)) {
          ids.push(category.id)
        }
        setIds(category.items)
      }
    }

    if (props.activeCategories.length > 0) {
      const lastCategory = props.activeCategories[props.activeCategories.length - 1] as Record<string, any>
      ids.push(lastCategory.id)
      setIds(lastCategory.items)
    }

    return ids
  })

  const isVisibleProduct = (product: any) => (categoryIds.value.length === 0 || product.category_ids.some((catId: number) => categoryIds.value.includes(catId)))

  const productRows = computed(() => {
    const products = props.products.filter(p => isVisibleProduct(p))
    const columnsPerRow = smAndDown.value ? 1 : 2
    const rows = []
    for (let i = 0; i < products.length; i += columnsPerRow) {
      rows.push(products.slice(i, i + columnsPerRow))
    }
    return rows
  })
</script>

<template>
  <VVirtualScroll :items="productRows">
    <template #default="{ item: row, index }">
      <VRow :key="index">
        <VCol
          v-for="product in row"
          :key="product.id"
          cols="12"
          md="6"
          sm="12"
        >
          <Product :product="product" />
        </VCol>
      </VRow>
    </template>
  </VVirtualScroll>
</template>

<style lang="scss" scoped>
.v-virtual-scroll {
  overflow: hidden;
}
</style>
