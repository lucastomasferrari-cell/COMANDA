import type { TranslationRecord } from '@/modules/translation/contracts/Translation.ts'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { getTranslations, updateTranslation } from '@/modules/translation/api/translation.api.ts'

export function useLanguage () {
  const toast = useToast()
  const { t } = useI18n()

  const loadData: () => Promise<TranslationRecord[] | false> = async () => {
    try {
      return (await getTranslations()).data.body.records
    } catch {
      toast.error(t('core::errors.failed_to_load_data'))
      return false
    }
  }
  const update = async (key: string, locale: string, value: string | null) => {
    try {
      toast.success((await updateTranslation(key, locale, value)).data.message)
    } catch {
      /* Empty */
    }
  }

  const parseTitlePage = (
    title: string | null,
    params: Record<string, string> | null,
  ): string => {
    if (title === null) {
      return ''
    }

    const resolvedParams: Record<string, string> = {}

    if (params) {
      for (const [key, value] of Object.entries(params)) {
        resolvedParams[key] = t(value)
      }
    }

    return t(title, resolvedParams)
  }

  return { loadData, parseTitlePage, update }
}
