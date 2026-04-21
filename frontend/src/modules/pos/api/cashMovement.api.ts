import { http } from '@/modules/core/api/http.ts'

export function show (id: number) {
  return http.get(`/v1/pos/cash-movements/${id}`)
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/pos/cash-movements`, item)
}
