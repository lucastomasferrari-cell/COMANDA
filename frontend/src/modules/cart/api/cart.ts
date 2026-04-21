import { http } from '@/modules/core/api/http.ts'

export function index (cartId: string) {
  return http.get(`/v1/cart/${cartId}`)
}

export function clear (cartId: string) {
  return http.delete(`/v1/cart/${cartId}/clear`)
}

export function storeOrderType (cartId: string, type: string) {
  return http.post(`/v1/cart/${cartId}/order-types/${type}`)
}

export function removeOrderType (cartId: string) {
  return http.delete(`/v1/cart/${cartId}/order-types/`)
}

export function storeItem (cartId: string, params: Record<string, any>) {
  return http.post(`/v1/cart/${cartId}/items`, params)
}

export function updateItem (cartId: string, id: string, qty: number) {
  return http.put(`/v1/cart/${cartId}/items/${id}`, { qty })
}

export function deleteItem (cartId: string, id: string) {
  return http.delete(`/v1/cart/${cartId}/items/${id}`)
}

export function applyDiscount (cartId: string, id: number) {
  return http.post(`/v1/cart/${cartId}/discounts/${id}`)
}

export function removeDiscount (cartId: string) {
  return http.delete(`/v1/cart/${cartId}/discounts`)
}

export function addCustomer (cartId: string, id: number) {
  return http.post(`/v1/cart/${cartId}/customers/${id}`)
}

export function removeCustomer (cartId: string) {
  return http.delete(`/v1/cart/${cartId}/customers`)
}

export function applyVoucher (cartId: string, code: string) {
  return http.post(`/v1/cart/${cartId}/vouchers`, { code })
}

export function applyGift (cartId: string, id: number) {
  return http.post(`/v1/cart/${cartId}/gifts/${id}`)
}

export function storeAction (cartId: string, id: string, action: string, qty: number) {
  return http.post(`/v1/cart/${cartId}/items/${id}/action`, { action, qty })
}

export function removeAction (cartId: string, id: string, action: string) {
  return http.delete(`/v1/cart/${cartId}/items/${id}/action`, { params: { action } })
}
