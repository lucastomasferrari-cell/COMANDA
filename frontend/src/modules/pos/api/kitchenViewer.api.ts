import { http } from '@/modules/core/api/http.ts'

export function getOrders (branchId?: number | null) {
  return http.get('/v1/pos/kitchen-viewer/orders', { params: { branch_id: branchId } })
}

export function getConfiguration (branchId?: number | null) {
  return http.get('/v1/pos/kitchen-viewer/configuration', { params: { branch_id: branchId } })
}

export function moveToNextStatus (orderId: number | string, ids?: number[]) {
  return http.patch(`/v1/pos/kitchen-viewer/${orderId}/move-to-next-status`, { ids })
}
