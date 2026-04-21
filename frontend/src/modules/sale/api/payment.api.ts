import { http } from '@/modules/core/api/http.ts'

export function show (id: number) {
  return http.get(`/v1/payments/${id}`)
}
