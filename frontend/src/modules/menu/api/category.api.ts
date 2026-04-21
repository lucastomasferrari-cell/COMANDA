import { http } from '@/modules/core/api/http.ts'

export function index (menuId: number | null) {
  return http.get(`/v1/categories`, { params: { filters: { menu_id: menuId } } })
}

export function show (id: number, returnAllTranslations = false) {
  return http.get(`/v1/categories/${id}`, { params: { return_all_translations: returnAllTranslations } })
}

export function update (id: number, item: Record<string, any>) {
  return http.put(`/v1/categories/${id}`, item)
}

export function destroy (ids: number | string) {
  return http.delete(`/v1/categories/${ids}`)
}

export function updateTree (menuId: number, tree: Record<string, any>[]) {
  return http.put(`/v1/categories/${menuId}/tree`, { tree })
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/categories`, item)
}
