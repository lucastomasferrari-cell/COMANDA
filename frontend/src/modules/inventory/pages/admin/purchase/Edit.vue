<script lang="ts" setup>
import {onBeforeMount, ref} from 'vue'
import {useRoute} from 'vue-router'
import {usePurchase} from '@/modules/inventory/composables/purchase.ts'
import CustomForm from './Partials/Form/Form.vue'

const {getShowData} = usePurchase()
const route = useRoute()

const loading = ref(false)
const isNotFound = ref(false)
const item = ref<Record<string, any> | null>(null)

onBeforeMount(async () => {
  loading.value = true
  const response = await getShowData((route.params as Record<string, any>).id, true)
  if (response.status === 200) {
    if (response.data.allow_edit) {
      item.value = response.data
    } else {
      isNotFound.value = true
    }
  } else if (response.status === 404) {
    isNotFound.value = true
  }
  loading.value = false
})

</script>

<template>
  <PageStateWrapper :loading="loading" :not-found="isNotFound">
    <CustomForm :item="item" action="update"/>
  </PageStateWrapper>
</template>
