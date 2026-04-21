import { http } from '@/modules/core/api/http.ts'

export function getAppSettings () {
  return http.get('/v1/app/settings')
}

export function getAppBootMeta () {
  return http.get('/v1/app/boot-meta')
}

export function getSettings (section: string) {
  return http.get(`/v1/settings/${section}`)
}

export function update (section: string, data: object) {
  return http.put(`/v1/settings/${section}/update`, data)
}
