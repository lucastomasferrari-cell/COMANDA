<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import NavbarAction from '@/app/layouts/components/NavbarAction.vue'
  import { useAppTheme } from '@/modules/core/composables/appTheme.ts'

  // Usa useAppTheme que envuelve Vuetify + persistencia en appStore. Antes
  // había useCycleList + lógica de next/change dispersa; esto concentra
  // todo en el composable (un único punto de mutación del tema).
  const { isDark, currentMode, toggleTheme } = useAppTheme()
  const { t } = useI18n()

  const icon = computed(() => (isDark.value ? 'tabler-moon' : 'tabler-sun'))
</script>

<template>
  <NavbarAction>
    <VTooltip>
      <template #activator="{ props:tooltipProps }">
        <VBtn
          color="default"
          :icon="icon"
          v-bind="tooltipProps"
          variant="text"
          @click="toggleTheme"
        />
      </template>
      <span>{{ t(`admin::navbar.theme_modes.${currentMode}`) }}</span>
    </VTooltip>
  </NavbarAction>
</template>
