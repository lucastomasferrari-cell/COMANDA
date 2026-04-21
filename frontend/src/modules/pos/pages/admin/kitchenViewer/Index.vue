<script lang="ts" setup>
  import type { AxiosError } from 'axios'
  import { computed, onBeforeMount, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'

  import { useKitchenAutoReload } from '@/modules/pos/composables/kitchenAutoReload'
  import { useKitchenViewer } from '@/modules/pos/composables/kitchenViewer'

  import KitchenHeader from '@/modules/pos/pages/admin/kitchenViewer/Partials/KitchenHeader.vue'
  import Orders from './Partials/Orders/Index.vue'

  const toast = useToast()
  const { t } = useI18n()
  const { getConfiguration, getOrders } = useKitchenViewer()

  const orders = ref<any[]>([])
  const lastUpdatedAt = ref<string | null>(null)

  const meta = ref<{
    branches: any[]
    orderTypes: any[]
    settings: any
  }>({
    branches: [],
    orderTypes: [],
    settings: {},
  })

  const loading = ref(true)
  const loadingConfiguration = ref(true)
  const refreshing = ref(false)
  const reloadBranchData = ref(false)

  const filters = ref<{
    branchId: number | null
    type: string | null
    searchQuery: string | null
  }>({
    branchId: null,
    type: null,
    searchQuery: null,
  })

  const loadOrders = async (
    branchId?: number | null,
    isRefreshing = false,
  ) => {
    isRefreshing ? (refreshing.value = true) : (loading.value = true)

    try {
      const response = await getOrders(branchId)
      orders.value = response.data.body.orders
      lastUpdatedAt.value = response.data.body.last_updated_at
    } catch (error) {
      toast.error(
        (error as AxiosError<{
          message?: string
        }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'),
      )
    } finally {
      loading.value = false
      refreshing.value = false
    }
  }

  const loadConfiguration = async (branchId?: number | null) => {
    branchId ? (reloadBranchData.value = true) : (loadingConfiguration.value = true)

    try {
      orders.value = []
      const response = await getConfiguration(branchId)

      if (!branchId) {
        meta.value.branches = response.data.body.branches
        meta.value.settings = response.data.body.settings
      }

      meta.value.orderTypes = response.data.body.order_types
      filters.value.branchId = response.data.body.branch_id

      await loadOrders(filters.value.branchId)
    } catch (error) {
      toast.error(
        (error as AxiosError<{
          message?: string
        }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'),
      )
    } finally {
      loadingConfiguration.value = false
      reloadBranchData.value = false
    }
  }

  // ===== Auto Reload (ONE TIME) =====
  const autoReloadSettings = computed(
    () => meta.value.settings?.auto_refresh ?? null,
  )

  useKitchenAutoReload({
    settings: autoReloadSettings,
    fetchData: async () => {
      const response = await getOrders(filters.value.branchId)

      orders.value = response.data.body.orders

      return {
        last_updated_at: response.data.body.last_updated_at,
      }
    },
  })

  watch(
    () => filters.value.branchId,
    async (newVal, oldVal) => {
      if (newVal && newVal !== oldVal) {
        await loadConfiguration(newVal)
      }
    },
  )

  const ordersFiltered = computed(() =>
    orders.value.filter(order => {
      let match = true

      if (filters.value.type) {
        match &&= filters.value.type === order.type?.id
      }

      if (filters.value.searchQuery) {
        const q = filters.value.searchQuery.toLowerCase()
        match
          &&= order.order_number?.toLowerCase().includes(q)
            || order.reference_no?.toLowerCase().includes(q)
      }

      return match
    }),
  )

  onBeforeMount(loadConfiguration)
</script>

<template>
  <KitchenHeader
    v-if="!loadingConfiguration"
    :filters="filters"
    :loading="loading"
    :meta="meta"
    :refreshing="refreshing"
    :reload-branch-data="reloadBranchData"
    @refresh="loadOrders(filters.branchId, true)"
  />

  <PageStateWrapper :loading="loadingConfiguration || loading">
    <Orders
      :orders="ordersFiltered"
      @refresh="loadOrders(filters.branchId, true)"
    />
  </PageStateWrapper>
</template>

<style lang="scss">
.layout-wrapper.layout-nav-type-vertical.pos-layout .layout-navbar,
.layout-wrapper.layout-nav-type-vertical.pos-layout .layout-footer {
  padding-inline: 0 !important;
}

.layout-wrapper.layout-nav-type-vertical.pos-layout .layout-footer {
  display: none;
}

.layout-wrapper.layout-nav-type-vertical.pos-layout .layout-navbar .navbar-content-container {
  box-shadow: none !important;
}

.pos-layout .layout-page-content {
  padding-block: 0.5rem !important;
  padding-inline: 0.5rem !important;
}

.layout-wrapper.layout-nav-type-vertical.pos-layout:not(.layout-navbar-sticky) .layout-navbar .navbar-content-container {
  margin-block-start: 0 !important;
}

</style>
