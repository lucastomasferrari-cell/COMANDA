<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  const tabs = computed(() => [
    { label: t('admin::sidebar.printers'), to: { name: 'admin.cocina.impresoras' } },
    { label: t('admin::sidebar.kds'), to: { name: 'admin.cocina.kds' } },
    { label: t('admin::sidebar.print_rules'), to: { name: 'admin.cocina.reglas' } },
    { label: t('setting::settings.sections.kitchen'), to: { name: 'admin.cocina.ajustes' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
