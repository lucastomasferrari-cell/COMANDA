import { http } from '@/modules/core/api/http.ts'

export function refresh () {
  return http.put('/v1/currency-rates/refresh')
}

export function getCurrencyRate (id: number) {
  return http.get(`/v1/currency-rates/${id}`)
}

export function updateCurrencyRate (id: number, rate: number) {
  return http.put(`/v1/currency-rates/${id}`, { rate })
}
