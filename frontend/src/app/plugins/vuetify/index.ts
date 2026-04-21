import type {App} from 'vue'
import {createVuetify} from 'vuetify'
import {VBtn} from 'vuetify/components/VBtn'
import {ar, en} from 'vuetify/locale'
import defaults from './defaults.ts'

import {icons} from './icons.ts'
import {applyThemeSettings, themes} from './theme.ts'
import '@/assets/scss/template/libs/vuetify/index.scss'
import 'vuetify/styles'
import {useAppStore} from "@/modules/core/stores/appStore.ts";

const vuetify = createVuetify({
  defaults,
  icons,
  aliases: {
    IconBtn: VBtn,
  },
  locale: {
    locale: 'en',
    fallback: 'en',
    messages: {en, ar},
  },
  theme: {
    defaultTheme: 'light',
    themes,
  },
})

export function setupVuetify(app: App<Element>) {
  const appStore = useAppStore()

  vuetify.locale.current.value = appStore.appCurrentLocale
  vuetify.locale.fallback.value = appStore.fallbackLocale
  applyThemeSettings(vuetify.theme, appStore.settings)
  vuetify.theme.change(appStore.appThemeMode)

  app.use(vuetify)
}

export function syncVuetifyThemeSettings() {
  const appStore = useAppStore()

  applyThemeSettings(vuetify.theme, appStore.settings)
  vuetify.theme.change(appStore.appThemeMode)
}

export default vuetify
