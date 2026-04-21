<script lang="ts" setup>
  import { useOnlineMenu } from '@/modules/menu/composables/onlineMenu.ts'
  import Categories from './Partials/Categories/Index.vue'
  import Products from './Partials/Products/Index.vue'

  const { getOnlineMenuData } = useOnlineMenu()
  const route = useRoute()

  const loading = ref(true)
  const isNotFound = ref(false)
  const data = ref<Record<string, any> | null>(null)
  const activeCategories = ref<Record<string, any>[]>([])

  function onChangeRootCategory (category?: Record<string, any> | null) {
    activeCategories.value = category ? [category] : []
  }

  function onChangeSubCategory (subCategory: Record<string, any> | null, level: number) {
    if (!subCategory) {
      activeCategories.value = activeCategories.value.slice(0, level)
      return
    }

    activeCategories.value = [
      ...activeCategories.value.slice(0, level),
      subCategory,
    ]
  }

  onBeforeMount(async () => {
    await getData()
  })

  async function getData () {
    try {
      loading.value = true
      const response = await getOnlineMenuData((route.params as Record<string, any>).slug)
      data.value = response.data.body
    } catch {
      isNotFound.value = true
    } finally {
      loading.value = false
    }
  }

</script>

<template>
  <PageStateWrapper :loading="loading" :not-found="isNotFound">
    <VContainer v-if="data">
      <VRow>
        <VCol cols="12">
          <Categories
            :active-categories="activeCategories"
            :categories="data.categories"
            @change-root="onChangeRootCategory"
            @change-sub="onChangeSubCategory"
          />
          <Products
            :active-categories="activeCategories"
            :products="data.products"
          />
        </VCol>
      </VRow>
    </VContainer>
  </PageStateWrapper>
</template>

<style lang="scss" scoped>

</style>
