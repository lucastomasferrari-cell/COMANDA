import { http } from '@/modules/core/api/http.ts'

export function show (id: number, menuId: number | null, returnAllTranslations = false) {
  return http.get(`/v1/products/${id}`, {
    params: {
      menuId,
      return_all_translations: returnAllTranslations,
    },
  })
}

export function update (id: number, item: Record<string, any>) {
  return http.put(`/v1/products/${id}`, item)
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/products`, item)
}

export function getFormMeta (menuId: number | null) {
  return http.get(`/v1/products/form/meta`, { params: { menu_id: menuId } })
}
