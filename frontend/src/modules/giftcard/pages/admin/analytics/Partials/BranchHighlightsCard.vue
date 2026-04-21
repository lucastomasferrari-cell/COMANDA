<script lang="ts" setup>
  import { ref, watch } from 'vue'
  import { useGiftCardAnalytics } from '@/modules/giftcard/composables/giftCardAnalytics.ts'

  const props = defineProps<{
    filters: Record<string, any>
    filtersLoading?: boolean
    currency: string
    precision: number
  }>()

  const { getGiftCardAnalyticsSection } = useGiftCardAnalytics()

  const loading = ref(false)
  const data = ref<Record<string, any>>({
    highlights: [],
  })

  async function load () {
    loading.value = true

    try {
      const response = await getGiftCardAnalyticsSection('branches', props.filters || {})
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
      <VIcon class="analytics-card__title-icon" icon="tabler-git-branch" size="18" />
      <span>{{ $t('giftcard::gift_cards.analytics.branch_highlights') }}</span>
    </VCardTitle>
    <VCardText>

      <div class="branch-list mt-4">
        <div
          v-for="item in data.highlights"
          :key="`${item.branch_id}-${item.branch_name}`"
          class="branch-item"
        >
          <div>
            <div class="branch-name">{{ item.branch_name }}</div>
            <div class="branch-meta">{{ item.cards_count }}
              {{ $t('giftcard::gift_cards.analytics.cards') }}
            </div>
          </div>
          <div class="text-end">
            <div class="branch-money">{{ item.outstanding_balance?.formatted || '-' }}</div>
            <div class="branch-meta">{{ $t('giftcard::gift_cards.analytics.redeemed_value') }}:
              {{ item.redeemed_value?.formatted || '-' }}
            </div>
          </div>
        </div>
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

.branch-list {
  display: grid;
  gap: 10px;
  max-height: 300px;
  overflow-y: auto;
}

.branch-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px;
  border-radius: 14px;
  border: 1px dashed rgba(var(--v-theme-on-surface), .12);
  background: rgba(var(--v-theme-surface), .85);
}

.branch-name {
  font-size: 14px;
  font-weight: 700;
}

.branch-money {
  font-size: 16px;
  font-weight: 800;
}

.branch-meta {
  color: rgba(var(--v-theme-on-surface), .62);
  font-size: 12px;
}
</style>
