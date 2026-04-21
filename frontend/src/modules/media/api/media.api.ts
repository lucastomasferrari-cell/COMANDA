import { http } from '@/modules/core/api/http.ts'

export function get (params: Record<string, any> = {}) {
  return http.get(`/v1/media`, { params })
}

export function update (id: number, params: Record<string, any>) {
  return http.put(`/v1/media/${id}`, params)
}

export function destroy (ids: string) {
  return http.delete(`/v1/media/${ids}`)
}

export function createFolder (params: Record<string, any>) {
  return http.post('/v1/media/folder/store', params)
}
