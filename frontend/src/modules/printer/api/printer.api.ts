import { http } from '@/modules/core/api/http.ts'

export function show (id: number, returnAllTranslations = false) {
  return http.get(`/v1/printers/${id}`, { params: { return_all_translations: returnAllTranslations } })
}

export function update (id: number, item: Record<string, any>) {
  return http.put(`/v1/printers/${id}`, item)
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/printers`, item)
}

export function getFormMeta (branchId?: number) {
  return http.get(`/v1/printers/form/meta`, { params: { branch_id: branchId } })
}
