import type { IconValue } from 'vuetify/lib/composables/icons.js'
import type { DropdownItem } from '@/modules/core/contracts/DropdownItem.ts'

export interface TableHeader {
  title: string
  value: string
  hidden?: boolean
  sortable?: boolean
  sortable_key?: string
}

export interface TableAction {
  key: string
  label?: string
  icon?: IconValue
  color?: string
  permission?: string | string[]
  tooltip?: string
  loading?: boolean
  disabled?: boolean | ((item: Record<string, any>) => boolean)
  hidden?: boolean | ((item: Record<string, any>) => boolean)
  type?: string
  options?: Record<string, Record<string, any>>[]
  onClick?: (item: Record<string, any>) => void | Promise<void>
  confirm?: {
    title?: string
    message?: string
    confirmButtonText?: string
    cancelButtonText?: string
    confirmColor?: string
  }
}

export type TableFilterType = | 'text'
  | 'select'
  | 'checkbox'
  | 'switch'
  | 'date'
  | 'number'
  | 'boolean'

export interface TableFilterSchema {
  key: string
  label: string
  type: TableFilterType
  default?: any
  options?: DropdownItem[]
  multiple?: boolean
  clearable?: boolean
  hidden?: boolean
  placeholder?: string
  depends?: string
  menu?: boolean
  dense?: boolean
  cols?: number
  min?: string
  max?: string
  required?: boolean
}

export interface PaginationMeta {
  current_page: number
  from: number
  last_page: number
  per_page: number
  to: number
  total: number
}
