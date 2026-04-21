<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  import { useSetting } from '@/modules/setting/composables/setting.ts'

  const sections = [
    {
      key: 'general',
      title: 'setting::settings.sections.general',
      routeName: 'admin.settings.general',
      icon: 'tabler-settings-cog',
    },
    {
      key: 'application',
      title: 'setting::settings.sections.application',
      routeName: 'admin.settings.application',
      icon: 'tabler-settings-2',
    },
    {
      key: 'logo',
      title: 'setting::settings.sections.logo',
      routeName: 'admin.settings.logo',
      icon: 'tabler-api-app',
    },
    {
      key: 'appearance',
      title: 'setting::settings.sections.appearance',
      routeName: 'admin.settings.appearance',
      icon: 'tabler-palette',
    },
    {
      key: 'pwa',
      title: 'setting::settings.sections.pwa',
      routeName: 'admin.settings.pwa',
      icon: 'tabler-device-mobile-cog',
    },
    {
      key: 'kitchen',
      title: 'setting::settings.sections.kitchen',
      routeName: 'admin.settings.kitchen',
      icon: 'tabler-chef-hat',
    },
    {
      key: 'currency',
      title: 'setting::settings.sections.currency',
      routeName: 'admin.settings.currency',
      icon: 'tabler-currency-euro',
    },
    {
      key: 'mail',
      title: 'setting::settings.sections.mail',
      routeName: 'admin.settings.mail',
      icon: 'tabler-mail-cog',
    },
    {
      key: 'filesystem',
      title: 'setting::settings.sections.filesystem',
      routeName: 'admin.settings.filesystem',
      icon: 'tabler-database-cog',
    },
  ]

  const { store } = useSetting()
  const { t } = useI18n()
  const appStore = useAppStore()
  const route = useRoute()
  const isRootSettings = computed(() => route.name as string === 'admin.settings')
  const activeSection = computed(() => sections.find((item: Record<string, any>) => item.routeName === route.name))

</script>

<template>
  <VRow>
    <VCol cols="12" md="3">
      <VCard>
        <VCardTitle class="border-b pb-2">
          <div class="d-flex align-center gap-2">
            <img v-if="appStore.logo" alt="logo" class="app-logo" :src="appStore.logo">
            <p class="text-h6 font-weight-bold mb-0">
              {{ t("setting::settings.header_title", {app_name: appStore.appName}) }}
            </p>
          </div>
        </VCardTitle>
        <VList>
          <VListItem
            v-for="(section, index) in sections"
            :key="index"
            :disabled="store.loading && activeSection?.key !== section.key"
            :prepend-icon="section.icon"
            :title="t(section.title)"
            :to="{name:section.routeName}"
          >
            <template #append>
              <VProgressCircular
                v-if="store.loading && activeSection?.key === section.key"
                color="primary"
                indeterminate
                size="18"
                width="2"
              />
            </template>
          </VListItem>
        </VList>
      </VCard>
    </VCol>
    <VCol cols="12" md="9">
      <VCard v-if="!isRootSettings">
        <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-1 font-weight-bold text-h6">
          <VIcon :icon="activeSection?.icon" size="20" />
          {{ t(route.meta.title as string) }}
        </VCardTitle>
        <VCardText>
          <router-view />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style scoped>
.app-logo {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
