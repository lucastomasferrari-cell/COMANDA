import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { syncVuetifyThemeSettings } from '@/app/plugins/vuetify/index.ts'
import { useAppStore } from '@/modules/core/stores/appStore.ts'
import * as settingApi from '@/modules/setting/api/setting.api.ts'
import { useSettingsStore } from '@/modules/setting/stores/settingsStore.ts'

export function useSetting () {
  const toast = useToast()
  const appStore = useAppStore()

  const { t } = useI18n()
  const settingStore = useSettingsStore()
  const getSettings = async (section: string): Promise<any> => {
    settingStore.setLoading(true)
    try {
      const response = await settingApi.getSettings(section)
      settingStore.setLoading(false)
      return response.data.body
    } catch {
      toast.error(t('core::errors.failed_to_load_data'))
      settingStore.setLoading(false)
      return false
    }
  }

  const update = async (section: string, data: object) => {
    const response = await settingApi.update(section, data)
    appStore.setSettings(response.data.body.app_settings)
    if (section === 'appearance') {
      const themeMode = response.data.body.app_settings.default_theme_mode

      if (themeMode === 'light' || themeMode === 'dark') {
        appStore.setThemeMode(themeMode)
      }
    }

    syncVuetifyThemeSettings()
    return response
  }

  return { store: settingStore, getSettings, update }
}
