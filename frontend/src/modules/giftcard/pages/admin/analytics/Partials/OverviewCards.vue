<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { ref, watch } from 'vue'
  import KpiCard from '@/modules/admin/pages/admin/dashboard/Partials/KpiCard.vue'
  import { useGiftCardAnalytics } from '@/modules/giftcard/composables/giftCardAnalytics.ts'

  const props = defineProps<{
    filters: Record<string, any>
    filtersLoading?: boolean
    currency: string
    precision: number
  }>()

  const { getGiftCardAnalyticsSection } = useGiftCardAnalytics()
  const { t } = useI18n()

  const loading = ref(false)
  const data = ref<Record<string, any>>({})

  const cards = [
    { key: 'total_cards', icon: 'tabler-cards', color: '#3867D6' },
    { key: 'outstanding_balance', icon: 'tabler-wallet', color: '#10B981', money: true },
    { key: 'sold_value', icon: 'tabler-shopping-cart', color: '#8B5CF6', money: true },
    { key: 'redeemed_value', icon: 'tabler-receipt-2', color: '#e15f41', money: true },
    { key: 'total_transactions', icon: 'tabler-arrows-exchange', color: '#0EA5E9' },
    { key: 'total_batches', icon: 'tabler-stack-2', color: '#3dc1d3' },
    { key: 'batch_face_value', icon: 'tabler-cash-banknote', color: '#EC4899', money: true },
    { key: 'expiring_soon_cards', icon: 'tabler-calendar-exclamation', color: '#F59E0B' },
  ]

  async function load () {
    loading.value = true

    try {
      const response = await getGiftCardAnalyticsSection('overview', props.filters || {})
      data.value = response.data.body || {}
    } finally {
      loading.value = false
    }
  }

  watch(() => props.filters, load, { deep: true })
</script>

<template>
  <VRow class="mt-1">
    <KpiCard
      v-for="card in cards"
      :key="card.key"
      :color="card.color"
      :icon="card.icon"
      :loading="loading || filtersLoading"
      :title="t(`giftcard::gift_cards.analytics.${card.key}`)"
      :value="card.money ? (data?.[card.key]?.formatted ?? '-') : (data?.[card.key] ?? 0)"
    />
  </VRow>
</template>
