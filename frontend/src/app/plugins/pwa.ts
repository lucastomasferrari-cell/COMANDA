import { registerSW } from 'virtual:pwa-register'
import { localStorageEngine } from '@/modules/core/services/LocalStorageEngine.ts'
import { useAppStore } from '@/modules/core/stores/appStore.ts'

const PWA_ENABLED_STORAGE_KEY = 'pwa_enabled'

function buildPwaManifestUrl (appStore: Record<string, any>): string {
  const settings = appStore.settings
  const baseUrl = import.meta.env.BASE_URL || '/'
  const appUrl = new URL(baseUrl, window.location.origin)
  const iconUrl = new URL(settings.pwa_icon || settings.logo || settings.favicon || `${baseUrl}logo.png`, window.location.origin).href
  const fallbackIconUrl = new URL(settings.favicon || settings.logo || iconUrl, window.location.origin).href

  const manifest = {
    name: settings.pwa_name || settings.app_name,
    short_name: settings.pwa_short_name || settings.pwa_name || settings.app_name,
    description: settings.pwa_description,
    start_url: appUrl.href,
    scope: appUrl.href,
    display: 'standalone',
    background_color: settings.pwa_background_color,
    theme_color: settings.pwa_theme_color,
    icons: [
      {
        src: iconUrl,
        sizes: '192x192',
        purpose: 'any',
      },
      {
        src: iconUrl,
        sizes: '512x512',
        purpose: 'any maskable',
      },
      {
        src: fallbackIconUrl,
        sizes: '64x64',
        purpose: 'any',
      },
    ],
  }

  return `data:application/manifest+json;charset=utf-8,${encodeURIComponent(JSON.stringify(manifest))}`
}

export function setupPwa () {
  const appStore = useAppStore()
  const isOnline = typeof navigator === 'undefined' ? true : navigator.onLine
  const isPwaEnabled = appStore.settings.pwa_enabled
  const wasPwaEnabled = localStorageEngine.getItem(PWA_ENABLED_STORAGE_KEY) === 'true'

  if (isPwaEnabled || (!isOnline && wasPwaEnabled)) {
    if (isPwaEnabled) {
      localStorageEngine.setItem(PWA_ENABLED_STORAGE_KEY, 'true')
    }

    const manifestUrl = buildPwaManifestUrl(appStore)
    const manifestLink = document.querySelector<HTMLLinkElement>('link[rel="manifest"]') || document.createElement('link')

    manifestLink.setAttribute('rel', 'manifest')
    manifestLink.setAttribute('href', manifestUrl)

    if (!manifestLink.parentNode) {
      document.head.append(manifestLink)
    }

    registerSW({ immediate: true })
  } else {
    localStorageEngine.removeItem(PWA_ENABLED_STORAGE_KEY)
    document.querySelector<HTMLLinkElement>('link[rel="manifest"]')?.remove()

    if (isOnline && 'serviceWorker' in navigator) {
      void navigator.serviceWorker.getRegistrations().then(registrations => {
        for (const registration of registrations) {
          void registration.unregister()
        }
      })
    }
  }
}
