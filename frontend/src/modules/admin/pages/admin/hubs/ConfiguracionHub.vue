<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  // Hub Configuración rehecho en bloque 6: absorbe Personal (Usuarios+Permisos),
  // Cocina (Impresión) y agrega Correo + Soporte técnico. "Apariencia" y "PWA"
  // se eliminaron — un single-tenant AR no necesita re-tematizar y el PWA ya
  // viene armado vía Vite plugin.
  const tabs = computed(() => [
    { label: t('admin::sidebar.general'), to: { name: 'admin.configuracion.general' } },
    { label: t('admin::sidebar.users_and_permissions'), to: { name: 'admin.configuracion.usuarios_permisos' } },
    { label: t('admin::sidebar.printing'), to: { name: 'admin.configuracion.impresion' } },
    { label: t('admin::sidebar.mail'), to: { name: 'admin.configuracion.correo' } },
    { label: t('admin::sidebar.afip'), to: { name: 'admin.configuracion.afip' } },
    { label: t('admin::sidebar.antifraud'), to: { name: 'admin.configuracion.antifraud' } },
    { label: t('admin::sidebar.integrations'), to: { name: 'admin.configuracion.integraciones' } },
    { label: t('admin::sidebar.tools'), to: { name: 'admin.configuracion.herramientas' } },
    { label: t('admin::sidebar.support'), to: { name: 'admin.configuracion.soporte' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
