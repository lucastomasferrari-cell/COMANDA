<script lang="ts" setup>
import {computed, onBeforeMount, ref} from 'vue'
import {useRoute} from 'vue-router'
import {usePurchase} from '@/modules/inventory/composables/purchase.ts'
import PurchaseInfo from './Partials/Show/PurchaseInfo.vue'
import PurchaseItems from './Partials/Show/PurchaseItems.vue'
import PurchaseReceipts from './Partials/Show/PurchaseReceipts.vue'

const {getShowData} = usePurchase()
const route = useRoute()

const loading = ref(false)
const isNotFound = ref(false)
const item = ref<Record<string, any> | null>(null)

onBeforeMount(async () => {
  loading.value = true
  const response = await getShowData((route.params as Record<string, any>).id, false, true)
  if (response.status === 200) {
    item.value = response.data
  } else if (response.status === 404) {
    isNotFound.value = true
  }
  loading.value = false
})

const hasReceipts = computed(() => item.value && item.value.receipts && item.value.receipts.length > 0)
</script>

<template>
  <PageStateWrapper :loading="loading" :not-found="isNotFound">
    <VRow v-if="item">
      <VCol :md="hasReceipts?8:12" cols="12">
        <PurchaseInfo :item="item"/>
        <PurchaseItems :item="item"/>
      </VCol>
      <VCol v-if="hasReceipts" :md="4" cols="12">
        <PurchaseReceipts :item="item"/>
      </VCol>
    </VRow>
  </PageStateWrapper>
</template>
