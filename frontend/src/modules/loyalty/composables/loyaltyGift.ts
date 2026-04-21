import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import {
  getAvailable,
  getRewards,
  redeem,
} from '@/modules/loyalty/api/loyaltyGift.api.ts'

export function useLoyaltyGift () {
  const toast = useToast()
  const { t } = useI18n()

  const getAllRewards = async (customerId: number): Promise<Record<string, any>> => {
    try {
      return { status: 200, data: (await getRewards(customerId)).data.body }
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return { status: error?.response?.status }
    }
  }

  const getAvailableGifts = async (customerId: number): Promise<Record<string, any>> => {
    try {
      return { status: 200, data: (await getAvailable(customerId)).data.body }
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return { status: error?.response?.status }
    }
  }

  return { redeem, getAvailableGifts, getAllRewards }
}
