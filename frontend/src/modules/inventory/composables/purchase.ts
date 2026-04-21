import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import {
  getFormMeta,
  markAsReceived,
  show,
  store,
  update,
} from '@/modules/inventory/api/purchase.api.ts'

export function usePurchase () {
  const toast = useToast()
  const { t } = useI18n()

  const getShowData = async (id: number, returnAllTranslations = false, withReceipt = false): Promise<Record<string, any>> => {
    try {
      return { status: 200, data: (await show(id, returnAllTranslations, withReceipt)).data.body }
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return { status: error?.response?.status }
    }
  }

  return { getShowData, update, store, getFormMeta, markAsReceived }
}
