import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import {
  activeOrders,
  cancel,
  edit,
  getPaymentMeta,
  getPrintMeta,
  getUpdateStatusMeta,
  moveToNextStatus,
  printPreview,
  refund,
  show,
  store,
  storePayment,
  upcomingOrders,
  update,
} from '@/modules/sale/api/order.api.ts'

export function useOrder () {
  const toast = useToast()
  const { t } = useI18n()

  const getShowData = async (id: number | string): Promise<Record<string, any>> => {
    try {
      return { status: 200, data: (await show(id)).data.body }
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return { status: error?.response?.status }
    }
  }

  return {
    store,
    update,
    cancel,
    refund,
    getShowData,
    getUpdateStatusMeta,
    activeOrders,
    storePayment,
    upcomingOrders,
    getPaymentMeta,
    moveToNextStatus,
    edit,
    printPreview,
    getPrintMeta,
  }
}
