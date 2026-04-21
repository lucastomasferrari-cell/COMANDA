import { http } from '@/modules/core/api/http.ts'

export function getRewards (customerId: number) {
  return http.get(`/v1/loyalty-gifts/rewards`, { params: { customer_id: customerId } })
}

export function redeem (customerId: number, rewardId: number, qty: number) {
  return http.post(`/v1/loyalty-gifts/rewards/${rewardId}/redeem`, { customer_id: customerId, qty })
}

export function getAvailable (customerId: number) {
  return http.get(`/v1/loyalty-gifts/available`, { params: { customer_id: customerId } })
}
