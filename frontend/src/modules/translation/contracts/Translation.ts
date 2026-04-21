export interface TranslationRecord {
  key: string
  translations: Record<string, string>
}

export type TranslationRow = {
  key: string
} & Record<string, string>

export interface PaginationMeta {
  current_page: number
  from: number
  last_page: number
  per_page: number
  to: number
  total: number
}
