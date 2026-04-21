import { http } from '@/modules/core/api/http.ts'

export function show (id: number, returnAllTranslations = false, withReceipt = false) {
  return http.get(`/v1/purchases/${id}`, {
    params: {
      return_all_translations: returnAllTranslations,
      with_receipt: withReceipt,
    },
  })
}

export function update (id: number, item: Record<string, any>) {
  return http.put(`/v1/purchases/${id}`, item)
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/purchases`, item)
}

export function getFormMeta (branchId?: number) {
  return http.get(`/v1/purchases/form/meta`, { params: { branch_id: branchId } })
}

export function markAsReceived (id: number, params: Record<string, any>) {
  return http.post(`/v1/purchases/${id}/mark-as-received`, params)
}
