<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  const tabs = computed(() => [
    { label: t('admin::sidebar.users'), to: { name: 'admin.personal.usuarios' } },
    { label: t('admin::sidebar.jobs'), to: { name: 'admin.personal.jobs' } },
    { label: t('admin::sidebar.roles'), to: { name: 'admin.personal.permisos' } },
    { label: t('admin::sidebar.shifts'), to: { name: 'admin.personal.turnos' } },
    { label: t('admin::sidebar.pos_sessions'), to: { name: 'admin.personal.arqueos' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
