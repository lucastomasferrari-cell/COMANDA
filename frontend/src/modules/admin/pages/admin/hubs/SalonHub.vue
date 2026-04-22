<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  const tabs = computed(() => [
    { label: t('admin::sidebar.seating_plan'), to: { name: 'admin.salon.plano' } },
    { label: t('admin::sidebar.sales_channels'), to: { name: 'admin.salon.canales' } },
    { label: t('admin::sidebar.notifications'), to: { name: 'admin.salon.notificaciones' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
