import { http } from '@/modules/core/api/http.ts'

function toFormData (item: Record<string, any>, methodOverride?: 'PUT' | 'PATCH'): FormData {
  const formData = new FormData()

  for (const [key, value] of Object.entries(item)) {
    if (value === null || value === undefined) {
      continue
    }

    if (value instanceof File) {
      formData.append(key, value)
      continue
    }

    if (Array.isArray(value)) {
      for (const [index, entry] of value.entries()) {
        formData.append(`${key}[${index}]`, `${entry}`)
      }
      continue
    }

    if (typeof value === 'boolean') {
      formData.append(key, value ? '1' : '0')
      continue
    }

    formData.append(key, `${value}`)
  }

  if (methodOverride) {
    formData.append('_method', methodOverride)
  }

  return formData
}

export function show (id: number, returnAllTranslations = false) {
  return http.get(`/v1/customers/${id}`, { params: { return_all_translations: returnAllTranslations } })
}

export function update (id: number, item: Record<string, any>) {
  return http.post(`/v1/customers/${id}`, toFormData(item, 'PUT'), {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/customers`, toFormData(item), {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
}

export function getFormMeta () {
  return http.get(`/v1/customers/form/meta`)
}
