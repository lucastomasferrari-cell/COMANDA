<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  const tabs = computed(() => [
    { label: t('admin::sidebar.mail'), to: { name: 'admin.configuracion.sistema.correo' } },
    { label: t('admin::sidebar.afip'), to: { name: 'admin.configuracion.sistema.afip' } },
    { label: t('admin::sidebar.integrations'), to: { name: 'admin.configuracion.sistema.integraciones' } },
    { label: t('admin::sidebar.tools'), to: { name: 'admin.configuracion.sistema.herramientas' } },
    { label: t('admin::sidebar.support'), to: { name: 'admin.configuracion.sistema.soporte' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
