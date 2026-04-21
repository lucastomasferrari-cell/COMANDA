import type { App } from 'vue'
import { createI18n } from 'vue-i18n'
import { useAppStore } from '@/modules/core/stores/appStore.ts'

const i18n = createI18n({
  legacy: false,
  globalInjection: true,
  locale: 'en',
  fallbackLocale: 'en',
  messages: {},
})

export function setupI18n (app: App<Element>) {
  const appStore = useAppStore()

  i18n.global.locale.value = appStore.appCurrentLocale
  i18n.global.fallbackLocale.value = appStore.fallbackLocale
  for (const [locale, messages] of Object.entries(appStore.appTranslations)) {
    i18n.global.setLocaleMessage(locale, messages)
  }

  app.use(i18n)
}

export default i18n
