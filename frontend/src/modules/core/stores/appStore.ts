import type { AppSettings, AppState, SupportedLanguages } from '@/modules/core/contracts/AppState.ts'
import { defineStore } from 'pinia'
import { localStorageEngine } from '@/modules/core/services/LocalStorageEngine.ts'

const THEME_MODE_STORAGE_KEY = 'theme_mode'
const CURRENT_LOCALE_STORAGE_KEY = 'current_locale'
const LOGO_URL_STORAGE_KEY = 'logo_url'

export const useAppStore = defineStore('app', {
  state: (): AppState => ({
    settings: {
      supported_locales: ['en'],
      supported_languages: [{
        id: 'en',
        name: 'English',
      }],
      locale: 'en',
      fallback_locale: 'en',
      start_of_week: 'sunday',
      end_of_week: 'saturday',
      timezone: 'Asia/Amman',
      default_time_format: 'h:i A',
      currency: 'JOD',
      is_rtl: false,
      app_name: 'Forkiva',
      logo: null,
      logo_data_base64: null,
      favicon: null,
      default_theme_mode: 'light',
      theme_primary_color: '#F57C00',
      theme_secondary_color: '#043A63',
      theme_success_color: '#2ecc71',
      theme_info_color: '#03C3EC',
      theme_warning_color: '#f1c40f',
      theme_error_color: '#e74c3c',
      pwa_enabled: false,
      pwa_name: 'Forkiva',
      pwa_short_name: 'Forkiva',
      pwa_description: 'Forkiva restaurant POS and management system.',
      pwa_background_color: '#ffffff',
      pwa_theme_color: '#ffffff',
      pwa_icon: null,
    },
    translations: {},
    themeMode: 'light',
    currentLocale: 'en',
  }),
  getters: {
    supportedLocales: state => state.settings.supported_locales,
    supportedLanguages: state => state.settings.supported_languages,
    locale: state => state.settings.locale,
    appCurrentLocale: state => state.currentLocale,
    fallbackLocale: state => state.settings.fallback_locale,
    timezone: state => state.settings.timezone,
    currency: state => state.settings.currency,
    appName: state => state.settings.app_name,
    appTranslations: state => state.translations,
    appThemeMode: state => state.themeMode,
    currentLanguage: state => {
      return state.settings.supported_languages.find(lang => lang.id === state.currentLocale)
    },
    defaultLanguage: (state): SupportedLanguages => {
      return state.settings.supported_languages.find(lang => lang.id === state.settings.locale) as SupportedLanguages
    },
    isRtl: (state): boolean => {
      const locale = state.currentLocale || state.settings.locale || 'en'

      return state.settings.is_rtl || locale === 'ar' || locale.startsWith('ar-')
    },
    logo: (state): string | null => state.settings.logo,
    logoDataBase64: (state): string | null => state.settings.logo_data_base64,
    favicon: (state): string | null => state.settings.favicon,
  },
  actions: {
    syncDocumentLanguageDirection (locale?: string) {
      if (typeof document === 'undefined') {
        return
      }

      const resolvedLocale = locale || this.currentLocale || this.settings.locale || 'en'
      const isRtl = this.settings.is_rtl || resolvedLocale === 'ar' || resolvedLocale.startsWith('ar-')

      document.documentElement.setAttribute('lang', resolvedLocale)
      document.documentElement.setAttribute('dir', isRtl ? 'rtl' : 'ltr')
    },
    restoreApp () {
      const appValues = localStorageEngine.getItems([
        THEME_MODE_STORAGE_KEY,
        CURRENT_LOCALE_STORAGE_KEY,
      ])
      const mode = appValues[THEME_MODE_STORAGE_KEY]
      const currentLocale = appValues[CURRENT_LOCALE_STORAGE_KEY]

      this.themeMode = mode === 'dark' || mode === 'light' ? mode : 'light'
      this.currentLocale = currentLocale || 'en'
      this.syncDocumentLanguageDirection(this.currentLocale)
    },
    setSettings (settings: AppSettings) {
      this.settings = settings

      if (!localStorageEngine.hasItem(THEME_MODE_STORAGE_KEY)) {
        this.themeMode = settings.default_theme_mode
      }

      if (!settings.supported_locales.includes(this.currentLocale)) {
        this.setCurrentLocale(settings.locale)
      }

      this.syncDocumentLanguageDirection(this.currentLocale)

      if (settings.logo) {
        this.setLogo(settings.logo)
      }
      if (settings.favicon) {
        useFavicon(settings.favicon)
      }
    },
    setTranslations (translations: object) {
      this.translations = translations
    },
    setThemeMode (mode: 'light' | 'dark') {
      this.themeMode = mode
      localStorageEngine.setItem(THEME_MODE_STORAGE_KEY, mode)
    },
    setCurrentLocale (currentLocale: string) {
      this.currentLocale = currentLocale
      localStorageEngine.setItem(CURRENT_LOCALE_STORAGE_KEY, currentLocale)
      this.syncDocumentLanguageDirection(currentLocale)
    },
    setLogo (logoUrl: string) {
      localStorageEngine.setItem(LOGO_URL_STORAGE_KEY, logoUrl)
    },
  },
})
