import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { getFormMeta, getReport, show, store, update } from '@/modules/payment/api/paymentMethod.api.ts'

export function usePaymentMethod () {
  const toast = useToast()
  const { t } = useI18n()

  const getShowData = async (id: number): Promise<Record<string, any>> => {
    try {
      return { status: 200, data: (await show(id)).data.body }
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return { status: error?.response?.status }
    }
  }

  return { getShowData, update, store, getFormMeta, getReport }
}
