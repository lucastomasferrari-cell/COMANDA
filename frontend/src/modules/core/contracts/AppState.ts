export interface SupportedLanguages {
  id: string
  name: string
}

export interface AppSettings {
  supported_locales: string[]
  supported_languages: SupportedLanguages[]
  locale: string
  fallback_locale: string
  is_rtl: boolean
  start_of_week: string
  end_of_week: string
  timezone: string
  default_time_format?: string
  currency: string
  app_name: string
  logo: string | null
  logo_data_base64: string | null
  favicon: string | null
  default_theme_mode: 'light' | 'dark'
  theme_primary_color: string
  theme_secondary_color: string
  theme_success_color: string
  theme_info_color: string
  theme_warning_color: string
  theme_error_color: string
  pwa_enabled: boolean
  pwa_name: string
  pwa_short_name: string
  pwa_description: string
  pwa_background_color: string
  pwa_theme_color: string
  pwa_icon: string | null
}

export interface AppState {
  settings: AppSettings
  translations: object
  themeMode: 'light' | 'dark'
  currentLocale: string
}
