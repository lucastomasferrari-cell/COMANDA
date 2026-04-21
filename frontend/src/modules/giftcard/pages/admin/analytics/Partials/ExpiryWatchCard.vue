<script lang="ts" setup>
  import { ref, watch } from 'vue'
  import { useGiftCardAnalytics } from '@/modules/giftcard/composables/giftCardAnalytics.ts'

  const props = defineProps<{
    filters: Record<string, any>
    filtersLoading?: boolean
  }>()

  const { getGiftCardAnalyticsSection } = useGiftCardAnalytics()

  const loading = ref(false)
  const data = ref<Record<string, any>>({
    summary: {},
    cards: [],
  })

  async function load () {
    loading.value = true

    try {
      const response = await getGiftCardAnalyticsSection('expiry', props.filters || {})
      data.value = response.data.body || {}
    } finally {
      loading.value = false
    }
  }

  watch(() => props.filters, load, { deep: true })
</script>

<template>
  <VCard class="analytics-card">
    <VCardTitle class="analytics-card__title">
      <VIcon class="analytics-card__title-icon" icon="tabler-calendar-exclamation" size="18" />
      <span>{{ $t('giftcard::gift_cards.analytics.expiring_cards') }}</span>
    </VCardTitle>
    <VCardText>

      <div class="summary-strip mt-4">
        <div class="summary-pill">
          <span>{{ $t('giftcard::gift_cards.analytics.expiring_in_7_days') }}</span>
          <strong>{{ data.summary?.expiring_in_7_days ?? 0 }}</strong>
        </div>
        <div class="summary-pill">
          <span>{{ $t('giftcard::gift_cards.analytics.expiring_in_30_days') }}</span>
          <strong>{{ data.summary?.expiring_in_30_days ?? 0 }}</strong>
        </div>
        <div class="summary-pill">
          <span>{{ $t('giftcard::gift_cards.analytics.expired_cards') }}</span>
          <strong>{{ data.summary?.expired_cards ?? 0 }}</strong>
        </div>
      </div>

      <VTable class="mt-4" density="comfortable">
        <thead>
          <tr>
            <th>{{ $t('giftcard::gift_cards.table.code') }}</th>
            <th>{{ $t('giftcard::gift_cards.table.customer') }}</th>
            <th>{{ $t('giftcard::gift_cards.table.branch') }}</th>
            <th>{{ $t('giftcard::gift_cards.table.expiry_date') }}</th>
            <th>{{ $t('giftcard::gift_cards.table.current_balance') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in data.cards" :key="item.id">
            <td>{{ item.code }}</td>
            <td>{{ item.customer || '-' }}</td>
            <td>{{ item.branch || $t('giftcard::gift_cards.analytics.global_pool') }}</td>
            <td>{{ item.expiry_date || '-' }}</td>
            <td>{{ item.current_balance?.formatted || '-' }}</td>
          </tr>
        </tbody>
      </VTable>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.analytics-card {
  min-height: 320px;
  border-radius: 14px;
}

.analytics-card__title {
  display: flex;
  align-items: center;
  gap: 8px;
}

.analytics-card__title-icon {
  color: rgb(var(--v-theme-primary));
}

.summary-strip {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.summary-pill {
  display: grid;
  gap: 4px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px dashed rgba(var(--v-theme-on-surface), .12);
  background: rgba(var(--v-theme-surface), .85);
}

.summary-pill span {
  font-size: 12px;
  color: rgba(var(--v-theme-on-surface), .65);
}

.summary-pill strong {
  font-size: 18px;
}

@media (max-width: 700px) {
  .summary-strip {
    grid-template-columns: minmax(0, 1fr);
  }
}
</style>
