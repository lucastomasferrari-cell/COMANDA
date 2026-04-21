<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import Row from './Row.vue'

  const props = defineProps<{
    order: Record<string, any>
  }>()
  const { t } = useI18n()
  const router = useRouter()

  const goToMainOrderDetails = () => {
    router.push({
      name: 'admin.orders.show',
      params: { id: props.order.merged_into_order.reference_no },
    } as unknown as RouteLocationRaw)
  }
</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('order::orders.show.cards.merged_info') }}
    </VCardTitle>
    <VCardText>
      <Row :label="t('order::orders.show.merged_by')" :value="order.merged_by.name" />
      <Row border :label="t('order::orders.show.merged_at')" :value="order.merged_at" />
      <Row class="mt-2" :label="t('order::orders.show.main_order_no')">
        <span class="text-primary text-decoration-underline">
          {{ order.merged_into_order.reference_no }}
        </span>
      </Row>
      <VBtn class="mt-4 w-100" color="default" variant="tonal" @click="goToMainOrderDetails">
        <VIcon color="info" icon="tabler-eye" start />
        {{ t('order::orders.show.view_order_details') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
