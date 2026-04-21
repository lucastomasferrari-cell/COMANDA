import { http } from '@/modules/core/api/http.ts'

export function index () {
  return http.get('/v1/tools/database')
}

export function backup () {
  return http.post('/v1/tools/database/backup')
}

export function restore (file: File) {
  const formData = new FormData()
  formData.append('file', file)

  return http.post('/v1/tools/database/restore', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  })
}

export function restoreFromBackup (fileName: string) {
  return http.post(`/v1/tools/database/restore/${encodeURIComponent(fileName)}`)
}

export function downloadBackup (fileName: string) {
  return http.get(`/v1/tools/database/download/${encodeURIComponent(fileName)}`, {
    responseType: 'blob',
  })
}
