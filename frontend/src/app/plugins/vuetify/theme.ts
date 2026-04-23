import type { ThemeDefinition } from 'vuetify'
import type { AppSettings } from '@/modules/core/contracts/AppState.ts'

// Paleta coral marca — Sprint 1.A. Reemplaza la paleta vendor (primary
// naranja #F57C00 + background frío #FAF8F3) por una identidad cálida
// gastronómica AR. Los tokens viven en Vuetify nativo; todo lo que ya
// usa rgba(var(--v-theme-*)) o clases bg-* text-* de Vuetify los
// hereda automáticamente sin tocar nada.
export const staticPrimaryColor = '#E8735A'
export const staticPrimaryDarkenColor = '#D85F48'
export const staticSecondaryColor = '#6B6259'
export const themes: Record<string, ThemeDefinition> = {
  light: {
    dark: false,
    colors: {
      // Surfaces
      'background': '#FDFBF7',        // blanco cálido
      'surface': '#FFFFFF',
      'surface-variant': '#FAF7F2',   // surface-elevated
      'on-background': '#1F1B17',
      'on-surface': '#1F1B17',
      'on-surface-variant': '#6B6259',

      // Brand
      'primary': staticPrimaryColor,
      'primary-darken-1': staticPrimaryDarkenColor,
      'primary-light': '#F2957E',
      'on-primary': '#FFFFFF',
      'secondary': staticSecondaryColor,
      'secondary-darken-1': '#524A43',
      'secondary-light': '#8A7F72',
      'on-secondary': '#FFFFFF',

      // Semantic gastronómico
      'success': '#0D9B6A',           // mesa libre, cocina lista
      'on-success': '#FFFFFF',
      'success-darken-1': '#0A7D55',
      'success-light': '#2FB787',
      'warning': '#F59E0B',           // en cocina
      'on-warning': '#1F1B17',
      'warning-darken-1': '#D48806',
      'warning-light': '#FBC65D',
      'error': '#DC2626',
      'on-error': '#FFFFFF',
      'error-darken-1': '#B91C1C',
      'error-light': '#EF5350',
      'info': '#3B82F6',
      'on-info': '#FFFFFF',
      'info-darken-1': '#2563EB',
      'info-light': '#60A5FA',

      // Tokens custom — KDS y estados de mesa
      'kitchen-hot': '#EF4444',       // comanda pasada de tiempo
      'table-occupied': '#F59E0B',
      'table-ready': '#0D9B6A',

      // Borders (custom)
      'border': '#EDE8E0',
      'border-strong': '#C9BFB2',

      // Greys — mantenemos la escala del vendor para no romper
      // componentes que la usan (VBtn color="grey-500", etc).
      'grey-50': '#FAFAFA',
      'grey-100': '#F5F5F5',
      'grey-200': '#EEEEEE',
      'grey-300': '#E0E0E0',
      'grey-400': '#BDBDBD',
      'grey-500': '#9E9E9E',
      'grey-600': '#757575',
      'grey-700': '#616161',
      'grey-800': '#424242',
      'grey-900': '#212121',
      'grey-light': '#FAFAFA',
      'perfect-scrollbar-thumb': '#DBDADE',
      'skin-bordered-background': '#fff',
      'skin-bordered-surface': '#fff',
      'expansion-panel-text-custom-bg': '#fafafa',
    },
    variables: {
      'code-color': '#d400ff',
      'overlay-scrim-background': '#1F1B17',
      'tooltip-background': '#1F1B17',
      'overlay-scrim-opacity': 0.5,
      'hover-opacity': 0.06,
      'focus-opacity': 0.1,
      'selected-opacity': 0.08,
      'activated-opacity': 0.16,
      'pressed-opacity': 0.14,
      'dragged-opacity': 0.1,
      'disabled-opacity': 0.4,
      'border-color': '#1F1B17',
      'border-opacity': 0.12,
      'table-header-color': '#FFFFFF',
      'high-emphasis-opacity': 0.9,
      'medium-emphasis-opacity': 0.7,
      'switch-opacity': 0.2,
      'switch-disabled-track-opacity': 0.3,
      'switch-disabled-thumb-opacity': 0.4,
      'switch-checked-disabled-opacity': 0.3,
      'track-bg': '#EEF1F3',
      'chat-bg': '#F7F8F8',
      // Shadows
      'shadow-key-umbra-color': '#1F1B17',
      'shadow-xs-opacity': 0.06,
      'shadow-sm-opacity': 0.06,
      'shadow-md-opacity': 0.06,
      'shadow-lg-opacity': 0.06,
      'shadow-xl-opacity': 0.06,
      'table-head-bg': '#FAF7F2',
      'on-table-head-bg': '#1F1B17',
    },
  },
  dark: {
    dark: true,
    colors: {
      // Surfaces — marrón-gris profundo (no negro puro) para gastronómico
      'background': '#1A1612',
      'surface': '#252019',
      'surface-variant': '#2F2921',
      'on-background': '#F5F0E8',
      'on-surface': '#F5F0E8',
      'on-surface-variant': '#A89C8D',

      // Brand — coral subido para AA en dark
      'primary': '#F08A6F',
      'primary-darken-1': staticPrimaryColor,
      'primary-light': '#F5A890',
      'on-primary': '#1A1612',
      'secondary': '#A89C8D',
      'secondary-darken-1': '#8A7F72',
      'secondary-light': '#C5BBAE',
      'on-secondary': '#1A1612',

      // Semantic
      'success': '#10B981',
      'on-success': '#1A1612',
      'success-darken-1': '#0D9B6A',
      'success-light': '#34D399',
      'warning': '#FBBF24',
      'on-warning': '#1A1612',
      'warning-darken-1': '#F59E0B',
      'warning-light': '#FCD34D',
      'error': '#EF4444',
      'on-error': '#FFFFFF',
      'error-darken-1': '#DC2626',
      'error-light': '#F87171',
      'info': '#60A5FA',
      'on-info': '#1A1612',
      'info-darken-1': '#3B82F6',
      'info-light': '#93C5FD',

      'kitchen-hot': '#F87171',
      'table-occupied': '#FBBF24',
      'table-ready': '#10B981',

      'border': '#3B3428',
      'border-strong': '#5C5244',

      // Greys dark (vendor scale)
      'grey-50': '#1F1B17',
      'grey-100': '#252019',
      'grey-200': '#2F2921',
      'grey-300': '#423A30',
      'grey-400': '#5C5244',
      'grey-500': '#7D7063',
      'grey-600': '#A89C8D',
      'grey-700': '#C5BBAE',
      'grey-800': '#DDD4C8',
      'grey-900': '#F5F0E8',
      'grey-light': '#2F2921',
      'perfect-scrollbar-thumb': '#5C5244',
      'skin-bordered-background': '#252019',
      'skin-bordered-surface': '#252019',
    },
    variables: {
      'code-color': '#d400ff',
      'overlay-scrim-background': '#0F0D0B',
      'tooltip-background': '#F5F0E8',
      'overlay-scrim-opacity': 0.6,
      'hover-opacity': 0.06,
      'focus-opacity': 0.1,
      'selected-opacity': 0.08,
      'activated-opacity': 0.16,
      'pressed-opacity': 0.14,
      'dragged-opacity': 0.1,
      'disabled-opacity': 0.4,
      'border-color': '#F5F0E8',
      'border-opacity': 0.12,
      'table-header-color': '#252019',
      'high-emphasis-opacity': 0.9,
      'medium-emphasis-opacity': 0.7,
      'switch-opacity': 0.4,
      'switch-disabled-track-opacity': 0.4,
      'switch-disabled-thumb-opacity': 0.8,
      'switch-checked-disabled-opacity': 0.3,
      'track-bg': '#3B3428',
      'chat-bg': '#1F1B17',
      // Shadows
      'shadow-key-umbra-color': '#0F0D0B',
      'shadow-xs-opacity': 0.18,
      'shadow-sm-opacity': 0.2,
      'shadow-md-opacity': 0.22,
      'shadow-lg-opacity': 0.24,
      'shadow-xl-opacity': 0.26,
      'table-head-bg': '#1F1B17',
      'on-table-head-bg': '#F5F0E8',
    },
  },
}

export function applyThemeSettings (theme: ThemeController, settings: AppSettings): void {
  for (const themeName of ['light', 'dark'] as const) {
    const colors = theme.themes.value[themeName]?.colors
    if (!colors) continue

    colors.primary = settings.theme_primary_color
    colors['primary-darken-1'] = settings.theme_primary_color
    colors.secondary = settings.theme_secondary_color
    colors['secondary-darken-1'] = settings.theme_secondary_color
    colors['secondary-light'] = settings.theme_secondary_color
    colors.success = settings.theme_success_color
    colors['success-darken-1'] = settings.theme_success_color
    colors.info = settings.theme_info_color
    colors['info-darken-1'] = settings.theme_info_color
    colors.warning = settings.theme_warning_color
    colors['warning-darken-1'] = settings.theme_warning_color
    colors.error = settings.theme_error_color
    colors['error-darken-1'] = settings.theme_error_color
  }

  theme.change(settings.default_theme_mode)
}

export default themes
type ThemeController = {
  themes: {
    value: Record<string, { colors: Record<string, string> }>
  }
  change: (name: string) => void
}
