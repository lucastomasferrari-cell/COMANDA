import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { show } from '@/modules/sale/api/invoice.api.ts'

export function useInvoice () {
  const { t } = useI18n()

  const getShowData = async (id: number | string): Promise<Record<string, any>> => {
    try {
      return { status: 200, data: (await show(id)).data.body }
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        useToast().error(t('core::errors.an_unexpected_error_occurred'))
      }
      return { status: error?.response?.status }
    }
  }

  const print = (invoice: Record<string, any>) => {
    window.open(invoice.pdf_url)
  }

  const download = (invoice: Record<string, any>) => {
    window.open(invoice.download_url)
  }

  return {
    print,
    download,
    getShowData,
  }
}
