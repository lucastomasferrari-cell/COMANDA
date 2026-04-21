import {http} from '@/modules/core/api/http.ts'

export function getAppTranslations() {
  return http.get('/v1/app/translations')
}

export function getTranslations() {
  return http.get('/v1/translations')
}

export function updateTranslation(key: string, locale: string, value: string | null) {
  return http.put(`/v1/translations/${key}`, {locale, value})
}
