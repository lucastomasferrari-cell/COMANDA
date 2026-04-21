import { http } from '@/modules/core/api/http.ts'

export function show (id: number, returnAllTranslations = false) {
  return http.get(`/v1/pos/sessions/${id}`, { params: { return_all_translations: returnAllTranslations } })
}

export function open (data: Record<string, any>) {
  return http.post(`/v1/pos/sessions/open`, data)
}

export function close (id: number, data: Record<string, any>) {
  return http.put(`/v1/pos/sessions/${id}/close`, data)
}

export function getFormMeta (branchId?: number) {
  return http.get(`/v1/pos/sessions/form/meta`, { params: { branch_id: branchId } })
}
