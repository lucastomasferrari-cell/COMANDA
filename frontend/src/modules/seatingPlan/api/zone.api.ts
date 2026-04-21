import { http } from '@/modules/core/api/http.ts'

export function show (id: number, returnAllTranslations = false) {
  return http.get(`/v1/zones/${id}`, { params: { return_all_translations: returnAllTranslations } })
}

export function update (id: number, item: Record<string, any>) {
  return http.put(`/v1/zones/${id}`, item)
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/zones`, item)
}

export function getFormMeta (branchId?: number) {
  return http.get(`/v1/zones/form/meta`, { params: { branch_id: branchId } })
}
