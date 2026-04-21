import type { MoneyValue, RefundPaymentMethod } from '@/modules/cart/composables/cart.ts'

export interface Category {
  id: number | string
  name?: string
  children?: Category[]
  products?: Product[]
  items?: Category[]
}

export interface Product {
  id: number | string
  name: string
  price?: MoneyValue
  badges?: string[]
  description?: string
  image?: string
  is_new?: boolean
  status?: { id: string; name?: string }
  category_ids?: Array<number | string>
  options?: any[]
  thumbnail?: string
  selling_price?: MoneyValue
}

export interface PosMeta {
  branches: any[]
  menus: any[]
  registers: any[]
  orderTypes: any[]
  categories: Category[]
  products: Product[]
  waiters: any[]
  customers: any[]
  discounts: any[]
  refundPaymentMethods: RefundPaymentMethod[]
  directions: any[]
  reasons: Record<string, any>
  order: any
  currency: string
  floors?: any[]
  zones?: any[]
  table_statuses?: any[]
}

export interface PosFormMeta {
  notes: string | null
  guestCount: number
  carPlate: string | null
  carDescription: string | null
  scheduledAt: string | null
}

export interface PosForm {
  mode: 'create' | 'edit'
  branchId: number | null
  menuId: number | null
  registerId: number | null
  waiter: any
  sessionId: number | null
  table: any
  skipFirst: boolean
  refundPaymentMethod: RefundPaymentMethod | string | number | null
  meta: PosFormMeta
  loading: boolean
  loadingMenuItems: boolean
  reloadBranchData: boolean
}
