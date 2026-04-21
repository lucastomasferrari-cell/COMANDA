<script lang="ts" setup>

import {useI18n} from 'vue-i18n'
import NavbarAction from "@/app/layouts/components/NavbarAction.vue";

const {t} = useI18n()

const switchFullscreen = () => {
  if (document.fullscreenElement) {
    if (document.exitFullscreen) {
      document.exitFullscreen()
    }
  } else {
    document.documentElement.requestFullscreen()
  }
}
document.addEventListener(
  'fullscreenchange',
  () => isFullscreen.value = document.fullscreenElement !== null,
)

const isFullscreen = ref(false)
</script>

<template>
  <VTooltip>
    <template #activator="{ props }">
      <NavbarAction v-bind="props">
        <VBtn :icon="isFullscreen ? 'bx-exit-fullscreen':'bx-fullscreen'" color='default'
              variant='text'
              @click="switchFullscreen"/>
      </NavbarAction>
    </template>
    <span>
      {{ t(`admin::navbar.fullscreen.${isFullscreen ? 'exit_fullscreen' : 'fullscreen'}`) }}
    </span>
  </VTooltip>
</template>
