import type { AxiosError } from 'axios'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import * as cartApi from '@/modules/cart/api/cart.ts'

export interface MoneyValue {
  amount: number
  formatted: string
  currency?: string
  precision?: number
}

export interface CartItemAction {
  id: string
  quantity: number
}

export interface CartItem {
  id: string
  item: { name: string }
  qty: number
  unitPrice: MoneyValue
  taxTotal: MoneyValue
  total: MoneyValue
  actions: CartItemAction[]
  loyaltyGift?: unknown
  options?: Array<{
    id: string | number
    name?: string
    values: Array<{ id: string | number, label?: string, price?: MoneyValue }>
  }>
  orderProduct?: { status?: { id: string } }
}

export interface Cart {
  items: CartItem[]
  quantity: number
  subTotal: MoneyValue
  orderType: {
    id: string | null
    name: string | null
  }
  taxes: any[]
  total: MoneyValue
  customer: any
  discount?: { id?: string | number, name?: string, amount?: MoneyValue, value?: MoneyValue } | null
  order?: Record<string, any> | null
}

export interface RefundPaymentMethod {
  id: number | string
  name: string
  icon?: string
  color?: string
}

const initCart: Cart = {
  items: [],
  quantity: 0,
  subTotal: { amount: 0, formatted: '0 JOD', currency: 'JOD', precision: 3 },
  orderType: {
    id: null,
    name: null,
  },
  taxes: [],
  total: { amount: 0, formatted: '0 JOD', currency: 'JOD', precision: 3 },
  customer: null,
}

export function useCart (cartId: string) {
  const toast = useToast()
  const { t } = useI18n()
  const data = ref<Cart>(structuredClone(initCart))
  const processing = ref<boolean>(false)

  const showError = (error: any) => {
    toast.error((error as AxiosError<{
      message?: string
    }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
  }

  const callApi = async (
    callback: (...args: any[]) => Promise<any>,
    ...params: any[]
  ): Promise<any> => {
    if (processing.value) {
      return
    }
    processing.value = true
    let isSuccess = false
    try {
      data.value = (await callback(cartId, ...params)).data.body
      isSuccess = true
    } catch (error) {
      showError(error)
    } finally {
      processing.value = false
    }
    return isSuccess
  }

  const getCart = async () => {
    await callApi(cartApi.index)
  }

  const clear = async () => {
    processing.value = true
    try {
      resetCart((await cartApi.clear(cartId)).data.body)
    } catch (error) {
      showError(error)
    } finally {
      processing.value = false
    }
  }

  const storeOrderType = async (type: string) => {
    await callApi(cartApi.storeOrderType, type)
  }

  const removeOrderType = async () => {
    await callApi(cartApi.removeOrderType)
  }

  const deleteItem = async (id: string) => {
    await callApi(cartApi.deleteItem, id)
  }

  const updateItem = async (id: string, qty: number) => {
    await callApi(cartApi.updateItem, id, qty)
  }

  const resetCart = (response?: Cart) => {
    const newCart = structuredClone(initCart)
    data.value = {
      ...newCart,
      ...(response || newCart),
    }
  }

  const storeProduct = (params: Record<string, any>) => {
    return cartApi.storeItem(cartId, params)
  }

  const storeAction = async (id: string, action: string, qty: number) => {
    await callApi(cartApi.storeAction, id, action, qty)
  }
  const removeAction = async (id: string, action: string) => {
    await callApi(cartApi.removeAction, id, action)
  }

  const applyDiscount = async (id: number) => {
    await callApi(cartApi.applyDiscount, id)
  }
  const applyVoucher = async (code: string) => {
    await callApi(cartApi.applyVoucher, code)
  }

  const removeDiscount = async () => {
    await callApi(cartApi.removeDiscount)
  }

  const addCustomer = async (id: number) => {
    await callApi(cartApi.addCustomer, id)
  }

  const applyGift = async (id: number): Promise<boolean> => {
    return await callApi(cartApi.applyGift, id)
  }

  const removeCustomer = async () => {
    await callApi(cartApi.removeCustomer)
  }

  return {
    data,
    cartId,
    processing,
    applyDiscount,
    applyGift,
    removeDiscount,
    addCustomer,
    removeCustomer,
    getCart,
    updateItem,
    storeItem: storeProduct,
    deleteItem,
    clear,
    storeOrderType,
    removeOrderType,
    resetCart,
    storeAction,
    removeAction,
    showError,
    applyVoucher,
  }
}

export type UseCart = ReturnType<typeof useCart>
