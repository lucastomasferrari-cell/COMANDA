import { http } from '@/modules/core/api/http.ts'

export interface VerifyPinPayload {
  user_id: number
  pin: string
  action_context?: string
}

export interface VerifyPinResponse {
  token: string
  expires_in: number
}

export function verifyManagerPin (payload: VerifyPinPayload) {
  return http.post<{ body: VerifyPinResponse }>(`/v1/auth/manager-pin/verify`, payload)
}

export function setSelfManagerPin (pin: string) {
  return http.post(`/v1/auth/manager-pin/self`, { pin })
}
