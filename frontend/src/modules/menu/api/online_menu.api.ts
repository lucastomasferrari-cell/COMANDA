import { http } from '@/modules/core/api/http.ts'

export function show (id: number, returnAllTranslations = false) {
  return http.get(`/v1/online-menus/${id}`, { params: { return_all_translations: returnAllTranslations } })
}

export function update (id: number, item: Record<string, any>) {
  return http.put(`/v1/online-menus/${id}`, item)
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/online-menus`, item)
}

export function getFormMeta (branchId?: number) {
  return http.get(`/v1/online-menus/form/meta`, { params: { branch_id: branchId } })
}

export function getOnlineMenuData (slug: string) {
  return http.get(`/v1/online-menus/${slug}/menu`)
}
