import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { getFormMeta, show, store, update } from '@/modules/menu/api/product.api.ts'

export function useProduct () {
  const toast = useToast()
  const { t } = useI18n()

  const getShowData = async (id: number, menuId: number | null, returnAllTranslations = false): Promise<Record<string, any>> => {
    try {
      return { status: 200, data: (await show(id, menuId, returnAllTranslations)).data.body }
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return { status: error?.response?.status }
    }
  }

  return { getShowData, update, store, getFormMeta }
}
