import { http } from '@/modules/core/api/http.ts'

export function getGiftCardAnalyticsFilters (filters: Record<string, any> = {}) {
  return http.get('/v1/gift-cards-analytics/filters', { params: { filters } })
}

export function getGiftCardAnalyticsSection (section: string, filters: Record<string, any> = {}) {
  return http.get('/v1/gift-cards-analytics', { params: { section, filters } })
}
