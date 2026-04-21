<script lang="ts" setup>
  import { onMounted, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { http } from '@/modules/core/api/http.ts'

  const props = defineProps<{
    filters: Record<string, any>
  }>()

  const { t } = useI18n()

  const loading = ref(true)
  const ingredients = ref<Record<string, any>[]>([])

  const fetchData = async () => {
    loading.value = true
    try {
      const response = await http.get('/v1/inventories/analytics/ingredient-purchases', {
        params: { ...props.filters },
      })
      ingredients.value = response.data.body
    } catch {} finally {
      loading.value = false
    }
  }

  onMounted(fetchData)

  watch(
    () => props.filters,
    () => {
      fetchData()
    },
    { deep: true },
  )

</script>

<template>
  <VCard style="height: 400px">
    <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-1 font-weight-bold text-h6">
      <VIcon color="primary" icon="ic-outline-fastfood" size="20" />
      {{ t('inventory::inventories.analytics.ingredient_purchases') }}
    </VCardTitle>
    <VCardText
      v-if="loading || ingredients.length === 0"
      class="d-flex justify-center align-center"
      style="height: calc(100% - 40px); position: relative;"
    >
      <VProgressCircular
        v-if="loading"
        color="primary"
        indeterminate
        size="40"
      />
      <div v-else-if="ingredients.length === 0" class="text-medium-emphasis">
        {{ t('inventory::inventories.analytics.no_data_available') }}
      </div>
    </VCardText>
    <VCardText v-else style="height: 400px;overflow-y: auto;">
      <p
        v-for="ingredient in ingredients"
        :key="ingredient.id"
        class="d-flex ingredient-item justify-space-between align-center w-100 mb-2"
      >
        <span>{{ ingredient.name }}</span>
        <span>{{ ingredient.total_quantity }}</span>
      </p>
    </VCardText>
  </VCard>
</template>
