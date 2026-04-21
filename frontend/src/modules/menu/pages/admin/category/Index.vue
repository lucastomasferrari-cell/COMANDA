<script lang="ts" setup>

  import { onMounted, ref } from 'vue'
  import { useRoute } from 'vue-router'
  import { useI18n } from 'vue-i18n'

  import { useToast } from 'vue-toastification'
  import { useCategory } from '@/modules/menu/composables/category.ts'
  import From from './Partials/Form.vue'
  import TreeView from './Partials/TreeView.vue'

  const { getIndexData, show } = useCategory()
  const route = useRoute()
  const toast = useToast()
  const { t } = useI18n()

  const loading = ref(true)
  const refreshing = ref(false)
  const isNotFound = ref(false)
  const data = ref<Record<string, any> | null>(null)
  const activeCategory = ref<Record<string, any> | null>(null)
  const parentId = ref(null)
  const action = ref<'update' | 'create'>('create')
  const treeViewKey = ref(`tree-view`)
  const formKey = ref(`form`)

  onMounted(() => {
    loadData()
  })

  const loadData = async (isRefreshing = false) => {
    if (isRefreshing) {
      refreshing.value = true
    } else {
      loading.value = true
    }
    const response = await getIndexData(data.value?.menu.id || (route.params as Record<string, any>).id)
    if (response.status === 200) {
      data.value = response.data
      reloadTreeView()
      reloadForm()
    } else if (!isRefreshing && response.status === 404) {
      isNotFound.value = true
    }
    loading.value = false
    refreshing.value = false
  }

  const onClickCategory = async (category: any) => {
    if (category.id != activeCategory.value?.id) {
      refreshing.value = true
      try {
        const response = await show(category.id, true)
        activeCategory.value = response.data.body
        action.value = 'update'
      } catch {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      reloadForm()
      refreshing.value = false
    }
  }

  const addSubCategory = () => {
    if (activeCategory.value) {
      parentId.value = activeCategory.value?.id
      action.value = 'create'
      reloadForm()
    }
  }
  const addRootCategory = () => {
    activeCategory.value = null
    parentId.value = null
    action.value = 'create'
    reloadForm()
  }

  const onSuccess = () => {
    addRootCategory()
    loadData(true)
  }

  const reloadTreeView = () => {
    treeViewKey.value = `tree-view-${Date.now()}`
  }
  const reloadForm = () => {
    formKey.value = `form-${Date.now()}`
  }

</script>

<template>
  <PageStateWrapper :loading="loading" :not-found="isNotFound">
    <VRow v-if="data!=null">
      <VCol cols="12" md="4">
        <TreeView
          :key="treeViewKey"
          :active-category="activeCategory"
          :categories="data.categories"
          :menu-id="data.menu.id"
          :refreshing="refreshing"
          @add-root-category="addRootCategory"
          @add-sub-category="addSubCategory"
          @click-category="onClickCategory"
        />
      </VCol>
      <VCol cols="12" md="8">
        <From
          :key="formKey"
          :action="action"
          :item="parentId?null: activeCategory"
          :menu-id="data.menu.id"
          :parent-id="parentId"
          @on-success="onSuccess"
        />
      </VCol>
    </VRow>
  </PageStateWrapper>
</template>
