<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'

  interface TabItem {
    label: string
    to: RouteLocationRaw
    icon?: string
  }

  defineProps<{
    tabs: TabItem[]
  }>()
</script>

<template>
  <div>
    <VTabs align-tabs="start" class="mb-4 page-tabs">
      <VTab
        v-for="tab in tabs"
        :key="typeof tab.to === 'string' ? tab.to : JSON.stringify(tab.to)"
        :to="tab.to"
        :prepend-icon="tab.icon"
      >
        {{ tab.label }}
      </VTab>
    </VTabs>
    <router-view />
  </div>
</template>

<style scoped>
.page-tabs {
  border-bottom: thin solid rgba(var(--v-theme-on-surface), 0.12);
}
</style>
