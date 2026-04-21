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
    top_batches: [],
  })

  async function load () {
    loading.value = true

    try {
      const response = await getGiftCardAnalyticsSection('batches', props.filters || {})
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
      <VIcon class="analytics-card__title-icon" icon="tabler-stack-2" size="18" />
      <span>{{ $t('giftcard::gift_cards.analytics.batch_activity') }}</span>
    </VCardTitle>
    <VCardText>

      <div class="summary-strip mt-4">
        <div class="summary-pill">
          <span>{{ $t('giftcard::gift_cards.analytics.total_batches') }}</span>
          <strong>{{ data.summary?.total_batches ?? 0 }}</strong>
        </div>
        <div class="summary-pill">
          <span>{{ $t('giftcard::gift_cards.analytics.total_batch_quantity') }}</span>
          <strong>{{ data.summary?.total_quantity ?? 0 }}</strong>
        </div>
        <div class="summary-pill wide">
          <span>{{ $t('giftcard::gift_cards.analytics.batch_face_value') }}</span>
          <strong>{{ data.summary?.total_value?.formatted ?? '-' }}</strong>
        </div>
      </div>

      <div class="batch-table-wrap mt-4">
        <VTable density="comfortable">
          <thead>
            <tr>
              <th>{{ $t('giftcard::gift_card_batches.gift_card_batch') }}</th>
              <th>{{ $t('giftcard::gift_cards.table.branch') }}</th>
              <th>{{ $t('giftcard::gift_cards.analytics.generated') }}</th>
              <th>{{ $t('giftcard::gift_cards.analytics.used') }}</th>
              <th>{{ $t('giftcard::gift_cards.analytics.remaining') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data.top_batches" :key="item.id">
              <td>{{ item.name }}</td>
              <td>{{ item.branch || $t('giftcard::gift_cards.analytics.global_pool') }}</td>
              <td>{{ item.cards_generated }}</td>
              <td>{{ item.cards_used }}</td>
              <td>{{ item.cards_remaining }}</td>
            </tr>
          </tbody>
        </VTable>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.analytics-card {
  min-height: 320px;
  height: 100%;
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

.wide {
  grid-column: span 1;
}

.batch-table-wrap {
  max-height: 300px;
  overflow-y: auto;
  overflow-x: auto;
}

@media (max-width: 700px) {
  .summary-strip {
    grid-template-columns: minmax(0, 1fr);
  }
}
</style>
