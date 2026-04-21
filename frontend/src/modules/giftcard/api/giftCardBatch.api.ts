import { http } from '@/modules/core/api/http.ts'

export function store (item: Record<string, any>) {
  return http.post('/v1/gift-card-batches', item)
}

export function getFormMeta () {
  return http.get('/v1/gift-card-batches/form/meta')
}
