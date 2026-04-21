import { http } from '@/modules/core/api/http.ts'

export function me () {
  return http.get('/v1/accounts/me')
}

export function updateProfile (data: Record<string, any>) {
  return http.put('/v1/accounts/profile/update', data)
}

export function updatePassword (data: Record<string, any>) {
  return http.put('/v1/accounts/password/update', data)
}
