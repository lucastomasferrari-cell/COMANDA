import { http } from '@/modules/core/api/http.ts'

export function show (id: number) {
  return http.get(`/v1/payment-methods/${id}`)
}

export function update (id: number, item: Record<string, any>) {
  return http.put(`/v1/payment-methods/${id}`, item)
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/payment-methods`, item)
}

export function getFormMeta () {
  return http.get(`/v1/payment-methods/form-meta`)
}

export function getReport (from: string, to: string) {
  return http.get(`/v1/payment-methods/report`, { params: { from, to } })
}
