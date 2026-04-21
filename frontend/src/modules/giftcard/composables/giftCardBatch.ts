import {
  getFormMeta,
  store,
} from '@/modules/giftcard/api/giftCardBatch.api.ts'

export function useGiftCardBatch () {
  return { store, getFormMeta }
}
