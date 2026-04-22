<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  // "Arqueos" se saco del hub Personal — el arqueo vive dentro de la
  // pantalla Caja del POS (acceso diario del cajero). El listado
  // historico de pos_sessions queda como ticket futuro cuando haya
  // necesidad de auditoria cruzada con reportes.
  const tabs = computed(() => [
    { label: t('admin::sidebar.users'), to: { name: 'admin.personal.usuarios' } },
    { label: t('admin::sidebar.jobs'), to: { name: 'admin.personal.jobs' } },
    { label: t('admin::sidebar.roles'), to: { name: 'admin.personal.permisos' } },
    { label: t('admin::sidebar.shifts'), to: { name: 'admin.personal.turnos' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
