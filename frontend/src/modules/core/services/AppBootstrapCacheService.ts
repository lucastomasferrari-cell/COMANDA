import type { AppSettings } from '@/modules/core/contracts/AppState.ts'
import { indexedDbEngine } from '@/modules/core/services/IndexedDbEngine.ts'

const APP_SETTINGS_CACHE_KEY = 'app_bootstrap_settings_cache'
const APP_TRANSLATIONS_CACHE_KEY = 'app_bootstrap_translations_cache'
const APP_BOOTSTRAP_META_CACHE_KEY = 'app_bootstrap_meta_cache'

interface CachedEntry<T> {
  value: T
  cachedAt: number
}

export interface AppBootstrapMeta {
  settingsVersion: string
  translationsVersion: string
}

export interface AppBootstrapCacheSnapshot {
  settings: CachedEntry<AppSettings> | null
  translations: CachedEntry<object> | null
  meta: CachedEntry<AppBootstrapMeta> | null
}

class AppBootstrapCacheService {
  async getSnapshot (): Promise<AppBootstrapCacheSnapshot> {
    const cachedValues = await indexedDbEngine.getItems([
      APP_SETTINGS_CACHE_KEY,
      APP_TRANSLATIONS_CACHE_KEY,
      APP_BOOTSTRAP_META_CACHE_KEY,
    ])

    return {
      settings: this.parseEntry<AppSettings>(cachedValues[APP_SETTINGS_CACHE_KEY]),
      translations: this.parseEntry<object>(cachedValues[APP_TRANSLATIONS_CACHE_KEY]),
      meta: this.parseEntry<AppBootstrapMeta>(cachedValues[APP_BOOTSTRAP_META_CACHE_KEY]),
    }
  }

  async store (settings: AppSettings, translations: object, meta: AppBootstrapMeta | null): Promise<void> {
    const entries: Record<string, string> = {
      [APP_SETTINGS_CACHE_KEY]: JSON.stringify(this.createEntry(settings)),
      [APP_TRANSLATIONS_CACHE_KEY]: JSON.stringify(this.createEntry(translations)),
    }

    if (meta) {
      entries[APP_BOOTSTRAP_META_CACHE_KEY] = JSON.stringify(this.createEntry(meta))
    }

    await indexedDbEngine.setItems(entries)

    if (!meta) {
      await indexedDbEngine.removeItem(APP_BOOTSTRAP_META_CACHE_KEY)
    }
  }

  async storeSettings (settings: AppSettings, meta: AppBootstrapMeta | null): Promise<void> {
    await indexedDbEngine.setItem(
      APP_SETTINGS_CACHE_KEY,
      JSON.stringify(this.createEntry(settings)),
    )

    await this.storeMeta(meta)
  }

  async storeTranslations (translations: object, meta: AppBootstrapMeta | null): Promise<void> {
    await indexedDbEngine.setItem(
      APP_TRANSLATIONS_CACHE_KEY,
      JSON.stringify(this.createEntry(translations)),
    )

    await this.storeMeta(meta)
  }

  hasCompleteCache (snapshot: AppBootstrapCacheSnapshot): boolean {
    return snapshot.settings !== null && snapshot.translations !== null
  }

  hasVersionedCache (snapshot: AppBootstrapCacheSnapshot): boolean {
    return this.hasCompleteCache(snapshot) && snapshot.meta !== null
  }

  matchesMeta (snapshot: AppBootstrapCacheSnapshot, meta: AppBootstrapMeta): boolean {
    return snapshot.meta?.value.settingsVersion === meta.settingsVersion
      && snapshot.meta?.value.translationsVersion === meta.translationsVersion
  }

  private async storeMeta (meta: AppBootstrapMeta | null): Promise<void> {
    if (!meta) {
      await indexedDbEngine.removeItem(APP_BOOTSTRAP_META_CACHE_KEY)
      return
    }

    await indexedDbEngine.setItem(
      APP_BOOTSTRAP_META_CACHE_KEY,
      JSON.stringify(this.createEntry(meta)),
    )
  }

  private createEntry<T> (value: T): CachedEntry<T> {
    return {
      value,
      cachedAt: Date.now(),
    }
  }

  private parseEntry<T> (value: string | null | undefined): CachedEntry<T> | null {
    if (!value) {
      return null
    }

    try {
      const parsed = JSON.parse(value) as Partial<CachedEntry<T>>

      if (typeof parsed.cachedAt !== 'number' || !('value' in parsed)) {
        return null
      }

      return parsed as CachedEntry<T>
    } catch {
      return null
    }
  }
}

export const appBootstrapCacheService = new AppBootstrapCacheService()

export default appBootstrapCacheService
