<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  const tabs = computed(() => [
    { label: t('admin::sidebar.users_and_permissions'), to: { name: 'admin.configuracion.usuarios_seguridad.usuarios_permisos' } },
    { label: t('admin::sidebar.antifraud'), to: { name: 'admin.configuracion.usuarios_seguridad.antifraud' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
