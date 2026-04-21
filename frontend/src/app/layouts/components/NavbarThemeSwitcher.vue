<script lang="ts" setup>
  import type { Theme } from '@/modules/core/contracts/Theme.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useTheme } from 'vuetify'
  import NavbarAction from '@/app/layouts/components/NavbarAction.vue'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  const themes: Theme[] = [
    {
      name: 'light',
      icon: 'tabler-sun',
    },
    {
      name: 'dark',
      icon: 'tabler-moon',
    },
  ]

  const { name: themeName, global: globalTheme, change } = useTheme()
  const {
    state: currentThemeName,
    next: getNextThemeName,
    index: currentThemeIndex,
  } = useCycleList(themes.map(t => t.name), { initialValue: themeName })
  const currentTheme = computed<Theme>(() => themes[currentThemeIndex.value ?? 0] ?? themes[0] ?? { name: 'light', icon: 'tabler-sun' })
  const appStore = useAppStore()

  function changeTheme () {
    change(getNextThemeName())
    if (globalTheme.name.value === 'light' || globalTheme.name.value === 'dark') {
      appStore.setThemeMode(globalTheme.name.value)
    }
  }

  watch(() => globalTheme.name.value, val => {
    currentThemeName.value = val
  })
  const { t } = useI18n()
</script>

<template>
  <NavbarAction>
    <VTooltip>
      <template #activator="{ props:tooltipProps }">
        <VBtn
          color="default"
          :icon="currentTheme.icon"
          v-bind="tooltipProps"
          variant="text"
          @click="changeTheme"
        />
      </template>
      <span>{{ t(`admin::navbar.theme_modes.${currentThemeName.toLowerCase()}`) }}</span>
    </VTooltip>
  </NavbarAction>
</template>
