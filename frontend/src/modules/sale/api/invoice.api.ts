import { http } from '@/modules/core/api/http.ts'

export function show (id: number | string) {
  return http.get(`/v1/invoices/${id}/show`)
}
