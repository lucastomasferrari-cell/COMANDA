export interface KitchenSettings {
  auto_refresh_enabled: boolean
  auto_refresh_mode: string | null
  auto_refresh_interval: number | null
  auto_refresh_pause_on_idle: boolean
  auto_refresh_idle_timeout: number | null
}

export interface KitchenSettingsMeta {
  modes: Record<string, any>[]
}

export interface KitchenSettingsResponse {
  settings: KitchenSettings
  meta: KitchenSettingsMeta
}

export interface LogoSettings {
  logo: Record<string, any> | null
  favicon: Record<string, any> | null
}

export interface LogoSettingsResponse {
  settings: LogoSettings
}

export interface AppearanceSettings {
  default_theme_mode: 'light' | 'dark'
  theme_primary_color: string
  theme_secondary_color: string
  theme_success_color: string
  theme_info_color: string
  theme_warning_color: string
  theme_error_color: string
}

export interface AppearanceSettingsMeta {
  theme_modes: Record<string, any>[]
}

export interface AppearanceSettingsResponse {
  settings: AppearanceSettings
  meta: AppearanceSettingsMeta
}

export interface PwaSettings {
  pwa_enabled: boolean
  pwa_name: string
  pwa_short_name: string
  pwa_icon: Record<string, any> | null
  pwa_background_color: string
  pwa_theme_color: string
  pwa_description: string | null
}

export interface PwaSettingsResponse {
  settings: PwaSettings
}

export interface GeneralSettings {
  supported_countries: string[]
  default_country: string
  supported_locales: string[]
  default_locale: string
  default_timezone: string
  default_date_format: string
  default_time_format: string
  start_of_week: string
  end_of_week: string
}

export interface GeneralSettingsMeta {
  countries: Record<string, any>[]
  locales: Record<string, any>[]
  timezones: Record<string, any>[]
  date_formats: Record<string, any>[]
  time_formats: Record<string, any>[]
  days: Record<string, any>[]
}

export interface GeneralSettingsResponse {
  settings: GeneralSettings
  meta: GeneralSettingsMeta
}

export interface CurrencySettings {
  supported_currencies: string[]
  default_currency: string
  currency_rate_exchange_service: string | null
  forge_api_key: string | null
  fixer_access_key: string | null
  currency_data_feed_api_key: string | null
  auto_refresh_currency_rates: boolean
  auto_refresh_currency_rate_frequency: string | null
}

export interface CurrencySettingsMeta {
  currencies: Record<string, any>[]
  frequencies: Record<string, any>[]
  exchange_services: Record<string, any>[]
}

export interface CurrencySettingsResponse {
  settings: CurrencySettings
  meta: CurrencySettingsMeta
}

export interface FilesystemSettings {
  default_filesystem_disk: string
  default_private_filesystem_disk: string
  filesystem_s3_public_use_path_style_endpoint: boolean
  filesystem_s3_public_url: string | null
  filesystem_s3_public_endpoint: string | null
  filesystem_s3_public_region: string | null
  filesystem_s3_public_bucket: string | null
  filesystem_s3_private_use_path_style_endpoint: boolean
  filesystem_s3_private_url: string | null
  filesystem_s3_private_endpoint: string | null
  filesystem_s3_private_region: string | null
  filesystem_s3_private_bucket: string | null
  encryptable: {
    filesystem_s3_public_key: string | null
    filesystem_s3_public_secret: string | null
    filesystem_s3_private_key: string | null
    filesystem_s3_private_secret: string | null
  }
}

export interface FilesystemSettingsMeta {
  disks: Record<string, any>[]
  private_disks: Record<string, any>[]
}

export interface FilesystemSettingsResponse {
  settings: FilesystemSettings
  meta: FilesystemSettingsMeta
}

export interface ApplicationSettings {
  app_name: Record<string, string>
}

export interface SupportedLanguagesState {
  id: string
  name: string
}

export interface ApplicationSettingsResponse {
  settings: ApplicationSettings
}

export interface MailSettingsResponse {
  settings: Record<string, any>
  meta: MailSettingsMeta
}

export interface MailSettingsMeta {
  mailers: Record<string, any>[]
  encryption_protocols: Record<string, any>[]
}
