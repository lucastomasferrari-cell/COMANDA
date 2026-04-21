import { http } from '@/modules/core/api/http.ts'

export function exportReport (key: string, method: string, filters: Record<string, any>) {
  return http.get(`/v1/reports/${key}/export/${method}`, { params: { filters }, responseType: 'blob' })
}
