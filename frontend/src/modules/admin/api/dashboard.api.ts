import type { AxiosResponse } from 'axios'
import { http } from '@/modules/core/api/http.ts'

const DASHBOARD_CACHE_TTL = 5 * 60 * 1000
const responseCache = new Map<string, {
  expiresAt: number
  response: AxiosResponse
}>()
const inFlightRequests = new Map<string, Promise<AxiosResponse>>()

function cachedGet<T = any> (cacheKey: string, url: string): Promise<AxiosResponse<T>> {
  const now = Date.now()
  const cached = responseCache.get(cacheKey)

  if (cached && cached.expiresAt > now) {
    return Promise.resolve(cached.response as AxiosResponse<T>)
  }

  const pending = inFlightRequests.get(cacheKey)
  if (pending) {
    return pending as Promise<AxiosResponse<T>>
  }

  const request = http.get<T>(url)
    .then(response => {
      responseCache.set(cacheKey, {
        expiresAt: Date.now() + DASHBOARD_CACHE_TTL,
        response,
      })

      return response
    })
    .finally(() => {
      inFlightRequests.delete(cacheKey)
    })

  inFlightRequests.set(cacheKey, request as Promise<AxiosResponse>)
  return request
}

export function clearDashboardCache () {
  responseCache.clear()
  inFlightRequests.clear()
}

export const overview = () => cachedGet('overview', '/v1/dashboards/overview')
export const salesAnalytics = (filter: string) => cachedGet(`sales-analytics:${filter}`, `/v1/dashboards/sales-analytics/${filter}`)
export const bestPerformingBranches = (filter: string) => cachedGet(`best-performing-branches:${filter}`, `/v1/dashboards/best-performing-branches/${filter}`)
export const orderTypeDistribution = (filter: string) => cachedGet(`order-type-distribution:${filter}`, `/v1/dashboards/order-type-distribution/${filter}`)
export const orderTotalByStatus = (filter: string) => cachedGet(`order-total-by-status:${filter}`, `/v1/dashboards/order-total-by-status/${filter}`)
export const paymentsOverview = (filter: string) => cachedGet(`payments-overview:${filter}`, `/v1/dashboards/payments-overview/${filter}`)

export const hourlySalesTrend = () => cachedGet('hourly-sales-trend', '/v1/dashboards/hourly-sales-trend')
export const branchWiseSalesComparison = (filter: string) => cachedGet(`branch-wise-sales-comparison:${filter}`, `/v1/dashboards/branch-wise-sales-comparison/${filter}`)
export const cashMovementsOverview = (filter: string) => cachedGet(`cash-movements-overview:${filter}`, `/v1/dashboards/cash-movements-overview/${filter}`)
export const topSellingProducts = (filter: string) => cachedGet(`top-selling-products:${filter}`, `/v1/dashboards/top-selling-products/${filter}`)
export const lowStockAlerts = () => cachedGet('low-stock-alerts', '/v1/dashboards/low-stock-alerts')
