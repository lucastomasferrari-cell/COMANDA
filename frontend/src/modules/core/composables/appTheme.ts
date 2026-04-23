import { computed } from 'vue'
import { useTheme } from 'vuetify'
import { useAppStore } from '@/modules/core/stores/appStore.ts'

type Mode = 'light' | 'dark'

/**
 * Wrapper sobre useTheme() de Vuetify que además persiste en localStorage
 * vía appStore y expone toggle simple para el botón sol/luna del header.
 *
 * El initTheme() del restoreApp() en appStore ya respeta
 * prefers-color-scheme la primera vez — este composable es la API pública
 * para mutarlo desde componentes.
 *
 * Uso típico:
 *   const { isDark, toggleTheme } = useAppTheme()
 *   <VBtn :icon="isDark ? 'tabler-moon' : 'tabler-sun'" @click="toggleTheme" />
 */
export function useAppTheme () {
  const theme = useTheme()
  const appStore = useAppStore()

  const isDark = computed(() => theme.global.current.value.dark)
  const currentMode = computed<Mode>(() => (isDark.value ? 'dark' : 'light'))

  const setTheme = (mode: Mode): void => {
    theme.global.name.value = mode
    appStore.setThemeMode(mode)
  }

  const toggleTheme = (): void => {
    setTheme(isDark.value ? 'light' : 'dark')
  }

  return {
    theme,
    isDark,
    currentMode,
    setTheme,
    toggleTheme,
  }
}
