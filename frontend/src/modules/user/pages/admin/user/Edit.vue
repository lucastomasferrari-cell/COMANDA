<script lang="ts" setup>
  import { useUser } from '@/modules/user/composables/user.ts'
  import CustomForm from './Partials/Form/Index.vue'

  const { getShowData } = useUser()
  const route = useRoute()

  const loading = ref(false)
  const isNotFound = ref(false)
  const item = ref<Record<string, any> | null>(null)

  onBeforeMount(async () => {
    loading.value = true
    const response = await getShowData((route.params as Record<string, any>).id, true)
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
    <CustomForm action="update" :item="item" />
  </PageStateWrapper>
</template>
