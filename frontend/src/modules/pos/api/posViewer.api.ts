import { http } from '@/modules/core/api/http.ts'

export function getConfiguration (cartId: string, branchId?: number) {
  return http.get(`/v1/pos/viewer/${cartId}/configuration`, { params: { branch_id: branchId } })
}

export function getMenuItems (cartId: string, menuId: number) {
  return http.get(`/v1/pos/viewer/${cartId}/menu-items/${menuId}`)
}
