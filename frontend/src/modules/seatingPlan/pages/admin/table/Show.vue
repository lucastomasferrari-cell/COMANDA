<script lang="ts" setup>
  import { useTable } from '@/modules/seatingPlan/composables/table.ts'
  import Details from './Partials/Show/Details.vue'
  import QrCode from './Partials/Show/QRCode.vue'
  import StatusHistory from './Partials/Show/StatusHistory.vue'

  const { getShowData } = useTable()
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

</script>

<template>
  <PageStateWrapper :loading="loading" :not-found="isNotFound">
    <VRow v-if="item">
      <VCol cols="12">
        <VRow>
          <VCol cols="12" md="8">
            <Details :item="item" />
            <StatusHistory :item="item" />
          </VCol>
          <VCol cols="12" md="4">
            <QrCode :item="item" />
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </PageStateWrapper>
</template>
