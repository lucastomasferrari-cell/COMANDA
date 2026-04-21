import { http } from '@/modules/core/api/http.ts'

interface TableViewerFilters {
  search?: string
  floors?: string[]
  zones?: string[]
  statuses?: string[]
}

export function show (id: number, returnAllTranslations = false) {
  return http.get(`/v1/tables/${id}`, { params: { return_all_translations: returnAllTranslations } })
}

export function update (id: number, item: Record<string, any>) {
  return http.put(`/v1/tables/${id}`, item)
}

export function store (item: Record<string, any>) {
  return http.post(`/v1/tables`, item)
}

export function getFormMeta (branchId?: number, floorId?: number) {
  return http.get(`/v1/tables/form/meta`, { params: { branch_id: branchId, floor_id: floorId } })
}

export function getTableViewer (branchId?: number | null, filters: TableViewerFilters = {}) {
  return http.get(`/v1/tables/viewer`, {
    params: {
      branch_id: branchId,
      ...filters,
    },
  })
}

export function getTableViewerDetails (id: number) {
  return http.get(`/v1/tables/viewer/${id}`)
}

export function assignWaiter (id: number, params: Record<string, any>) {
  return http.patch(`/v1/tables/viewer/${id}/assign-waiter`, params)
}

export function makeAvailable (id: number) {
  return http.patch(`/v1/tables/viewer/${id}/make-available`)
}

export function merge (id: number, params: Record<string, any>) {
  return http.post(`/v1/tables/viewer/${id}/merge`, params)
}

export function split (mergeId: number) {
  return http.post(`/v1/tables/viewer/${mergeId}/split`)
}

export function getMergeMeta (id: number) {
  return http.get(`/v1/tables/viewer/${id}/merge/meta`)
}
