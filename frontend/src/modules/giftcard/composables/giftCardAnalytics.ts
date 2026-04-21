import {
  getGiftCardAnalyticsFilters,
  getGiftCardAnalyticsSection,
} from '@/modules/giftcard/api/giftCardAnalytics.api.ts'

export function useGiftCardAnalytics () {
  return {
    getGiftCardAnalyticsFilters,
    getGiftCardAnalyticsSection,
  }
}
