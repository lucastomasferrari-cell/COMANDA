import { http } from '@/modules/core/api/http.ts'

export function login (identifier: string, password: string) {
  return http.post('/v1/auth/login', { identifier, password })
}

export function logout () {
  return http.post('/v1/auth/logout')
}

export function logoutByToken (token: string) {
  return http.post('/v1/auth/logout', {}, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  })
}

export function check () {
  return http.post('/v1/auth/check')
}
