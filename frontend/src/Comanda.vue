<script lang="ts" setup>
  import type { Component } from 'vue'
  import type { TargetName } from '@/modules/core/contracts/Target'
  import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
  import { RouterView, useRoute } from 'vue-router'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'
  import { useLanguage } from '@/modules/translation/composables/language.ts'
  import { resolveLayout } from './app/router/layoutResolver'

  const route = useRoute()
  const appStore = useAppStore()
  const { parseTitlePage } = useLanguage()

  const layout = computed<Component | void>(() => resolveLayout(route.meta.target as TargetName | undefined))
  const isPwaMode = ref(false)

  function detectPwaMode (): boolean {
    const nav = window.navigator as Navigator & { standalone?: boolean }

    return (
      window.matchMedia('(display-mode: standalone)').matches
      || window.matchMedia('(display-mode: minimal-ui)').matches
      || window.matchMedia('(display-mode: window-controls-overlay)').matches
      || nav.standalone === true
      || document.referrer.startsWith('android-app://')
    )
  }

  function syncPwaMode () {
    isPwaMode.value = detectPwaMode()
  }

  onMounted(() => {
    syncPwaMode()
    window.matchMedia('(display-mode: standalone)').addEventListener('change', syncPwaMode)
    window.matchMedia('(display-mode: minimal-ui)').addEventListener('change', syncPwaMode)
    window.matchMedia('(display-mode: window-controls-overlay)').addEventListener('change', syncPwaMode)
  })

  onBeforeUnmount(() => {
    window.matchMedia('(display-mode: standalone)').removeEventListener('change', syncPwaMode)
    window.matchMedia('(display-mode: minimal-ui)').removeEventListener('change', syncPwaMode)
    window.matchMedia('(display-mode: window-controls-overlay)').removeEventListener('change', syncPwaMode)
  })

  watch(
    [() => route.meta.title, isPwaMode],
    ([newTitle]) => {
      const pageTitle = newTitle ? parseTitlePage(newTitle as string, route.meta?.transParam as Record<string, string>) : appStore.appName

      useTitle(isPwaMode.value || !newTitle ? pageTitle : `${pageTitle} | ${appStore.appName}`)
    },
    { immediate: true },
  )

</script>

<template>
  <v-app>
    <component :is="layout">
      <RouterView />
    </component>
  </v-app>
</template>
