import type {App} from 'vue'
import {createVuetify} from 'vuetify'
import {VBtn} from 'vuetify/components/VBtn'
import {ar, en, es} from 'vuetify/locale'
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
    locale: 'es_AR',
    fallback: 'es_AR',
    messages: {en, ar, es, es_AR: es},
  },
  theme: {
    defaultTheme: 'light',
    themes,
    // variations genera primary-lighten-{1..4}, primary-darken-{1..4}, etc.
    // automáticamente desde el color base. Cubre hovers/actives sin definir
    // a mano cada tonalidad. Aplicado solo a primary/secondary/surface para
    // no inflar el CSS output con colores que no usamos.
    variations: {
      colors: ['primary', 'secondary', 'surface'],
      lighten: 4,
      darken: 4,
    },
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
