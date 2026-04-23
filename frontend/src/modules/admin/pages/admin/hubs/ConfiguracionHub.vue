<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  const tabs = computed(() => [
    { label: t('admin::sidebar.general'), to: { name: 'admin.configuracion.general' } },
    { label: t('admin::sidebar.appearance'), to: { name: 'admin.configuracion.apariencia' } },
    { label: t('admin::sidebar.afip'), to: { name: 'admin.configuracion.afip' } },
    { label: t('admin::sidebar.integrations'), to: { name: 'admin.configuracion.integraciones' } },
    { label: t('admin::sidebar.antifraud'), to: { name: 'admin.configuracion.antifraud' } },
    { label: t('admin::sidebar.tools'), to: { name: 'admin.configuracion.herramientas' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
