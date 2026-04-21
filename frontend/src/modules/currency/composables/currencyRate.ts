import type {AxiosError} from 'axios'
import {useI18n} from 'vue-i18n'
import {useToast} from 'vue-toastification'
import * as currencyRate from '@/modules/currency/api/currencyRate.api.ts'

export function useCurrencyRate() {
  const toast = useToast()
  const {t} = useI18n()

  const refresh = async () => {
    try {
      const response = await currencyRate.refresh()
      toast.success(response.data.message)
    } catch (error) {
      toast.error((error as AxiosError<{
        message?: string
      }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
    }
  }

  const getCurrencyRate = async (id: number): Promise<Record<string, any>> => {
    try {
      return {status: 200, data: (await currencyRate.getCurrencyRate(id)).data.body}
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return {status: error?.response?.status}
    }
  }

  return {refresh, getCurrencyRate, updateCurrencyRate: currencyRate.updateCurrencyRate}
}
