import { http } from '@/modules/core/api/http.ts'

export function show (id: number, returnAllTranslations = false) {
  return http.get(`/v1/taxes/${id}`, { params: { return_all_translations: returnAllTranslations } })
}

export function update (id: number, item: Record<string, any>) {
  return http.put(`/v1/taxes/${id}`, item)
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/taxes`, item)
}

export function getFormMeta (branchId?: number) {
  return http.get(`/v1/taxes/form/meta`, { params: { branch_id: branchId } })
}
