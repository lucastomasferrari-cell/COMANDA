import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { close, getFormMeta, open, show } from '@/modules/pos/api/session.api.ts'

export function usePosSession () {
  const toast = useToast()
  const { t } = useI18n()

  const getShowData = async (id: number, returnAllTranslations = false): Promise<Record<string, any>> => {
    try {
      return { status: 200, data: (await show(id, returnAllTranslations)).data.body }
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return { status: error?.response?.status }
    }
  }

  return { open, close, getFormMeta, getShowData }
}
