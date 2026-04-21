import {
  appBootstrapCacheService,
  type AppBootstrapCacheSnapshot,
  type AppBootstrapMeta,
} from '@/modules/core/services/AppBootstrapCacheService.ts'
import { useAppStore } from '@/modules/core/stores/appStore.ts'
import { getAppBootMeta, getAppSettings } from '@/modules/setting/api/setting.api.ts'
import { getAppTranslations } from '@/modules/translation/api/translation.api.ts'

export async function setupBootData (): Promise<void> {
  const appStore = useAppStore()
  appStore.restoreApp()

  const cachedBootstrap = await appBootstrapCacheService.getSnapshot()
  const cachedMeta = cachedBootstrap.meta?.value || null

  applyCachedBootData(cachedBootstrap)

  const latestMeta = await fetchBootMeta()
  const nextMeta: AppBootstrapMeta | null = latestMeta ?? cachedMeta

  await refreshSettingsIfNeeded(cachedBootstrap, cachedMeta, latestMeta, nextMeta)
  await refreshTranslationsIfNeeded(cachedBootstrap, cachedMeta, latestMeta, nextMeta)
}

async function fetchBootMeta (): Promise<AppBootstrapMeta | null> {
  try {
    const response = await getAppBootMeta()

    return {
      settingsVersion: response.data.body.settings_version,
      translationsVersion: response.data.body.translations_version,
    }
  } catch {
    return null
  }
}

function applyCachedBootData (cachedBootstrap: AppBootstrapCacheSnapshot): void {
  const appStore = useAppStore()

  if (cachedBootstrap.settings) {
    appStore.setSettings(cachedBootstrap.settings.value)
  }

  if (cachedBootstrap.translations) {
    appStore.setTranslations(cachedBootstrap.translations.value)
  }
}

async function refreshSettingsIfNeeded (
  cachedBootstrap: AppBootstrapCacheSnapshot,
  cachedMeta: AppBootstrapMeta | null,
  latestMeta: AppBootstrapMeta | null,
  nextMeta: AppBootstrapMeta | null,
): Promise<void> {
  if (!shouldRefreshSettings(cachedBootstrap, cachedMeta, latestMeta)) {
    return
  }

  try {
    const settings = (await getAppSettings()).data.body
    useAppStore().setSettings(settings)
    await appBootstrapCacheService.storeSettings(settings, nextMeta)
  } catch {
    if (!cachedBootstrap.settings) {
      console.error('Failed to retrieve app settings.')
    }
  }
}

async function refreshTranslationsIfNeeded (
  cachedBootstrap: AppBootstrapCacheSnapshot,
  cachedMeta: AppBootstrapMeta | null,
  latestMeta: AppBootstrapMeta | null,
  nextMeta: AppBootstrapMeta | null,
): Promise<void> {
  if (!shouldRefreshTranslations(cachedBootstrap, cachedMeta, latestMeta)) {
    return
  }

  try {
    const translations = (await getAppTranslations()).data.body
    useAppStore().setTranslations(translations)
    await appBootstrapCacheService.storeTranslations(translations, nextMeta)
  } catch {
    if (!cachedBootstrap.translations) {
      console.error('Failed to retrieve app translations.')
    }
  }
}

function shouldRefreshSettings (
  cachedBootstrap: AppBootstrapCacheSnapshot,
  cachedMeta: AppBootstrapMeta | null,
  latestMeta: AppBootstrapMeta | null,
): boolean {
  if (!cachedBootstrap.settings) {
    return true
  }

  if (!latestMeta) {
    return false
  }

  if (!cachedMeta) {
    return true
  }

  return cachedMeta.settingsVersion !== latestMeta.settingsVersion
}

function shouldRefreshTranslations (
  cachedBootstrap: AppBootstrapCacheSnapshot,
  cachedMeta: AppBootstrapMeta | null,
  latestMeta: AppBootstrapMeta | null,
): boolean {
  if (!cachedBootstrap.translations) {
    return true
  }

  if (!latestMeta) {
    return false
  }

  if (!cachedMeta) {
    return true
  }

  return cachedMeta.translationsVersion !== latestMeta.translationsVersion
}
