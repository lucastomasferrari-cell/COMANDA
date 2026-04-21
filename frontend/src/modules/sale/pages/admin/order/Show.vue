<script lang="ts" setup>
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import PageStateWrapper from '@/modules/core/components/PageStateWrapper.vue'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import Customer from '@/modules/sale/pages/admin/order/Partials/Show/Customer.vue'
  import Details from './Partials/Show/Details.vue'
  import Information from './Partials/Show/Information.vue'
  import Invoices from './Partials/Show/Invoices/Index.vue'
  import MergedInfo from './Partials/Show/MergedInfo/Index.vue'
  import Notes from './Partials/Show/Notes.vue'
  import Payments from './Partials/Show/Payments/Index.vue'
  import Products from './Partials/Show/Products/Index.vue'
  import StatusLogs from './Partials/Show/StatusLogs/Index.vue'
  import Summary from './Partials/Show/Summary/Index.vue'

  const { getShowData } = useOrder()
  const { can } = useAuth()
  const route = useRoute()

  const loading = ref(false)
  const isNotFound = ref(false)
  const item = ref<Record<string, any> | null>(null)

  onBeforeMount(async () => {
    loading.value = true
    const response = await getShowData((route.params as Record<string, any>).id)
    if (response.status === 200) {
      item.value = response.data
    } else if (response.status === 404) {
      isNotFound.value = true
    }
    loading.value = false
  })

  const isMerged = computed(() => item.value?.status.id === 'merged')

</script>

<template>
  <PageStateWrapper :loading="loading" :not-found="isNotFound">
    <VRow v-if="item">
      <VCol cols="12" md="9">
        <VRow>
          <VCol cols="12">
            <Information :order="item" />
          </VCol>
          <VCol v-if="item.customer" cols="12">
            <Customer :customer="item.customer" />
          </VCol>
          <VCol v-if="!isMerged" cols="12">
            <Products :products="item.products" />
          </VCol>
          <VCol cols="12">
            <StatusLogs :status-logs="item.status_logs" />
          </VCol>
        </VRow>
      </VCol>
      <VCol cols="12" md="3">
        <VRow>
          <VCol v-if="isMerged" cols="12">
            <MergedInfo :order="item" />
          </VCol>
          <VCol v-if="!isMerged" cols="12">
            <Summary :order="item" />
          </VCol>
          <Details v-if="!isMerged" :details="item.details" />
          <VCol v-if="!isMerged && can('admin.invoices.index') && item.invoices.length>0" cols="12">
            <Invoices :invoices="item.invoices" />
          </VCol>
          <VCol v-if="!isMerged" cols="12">
            <Notes :notes="item.notes" />
          </VCol>
          <VCol v-if="!isMerged" cols="12">
            <Payments :payments="item.payments" />
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </PageStateWrapper>
</template>
