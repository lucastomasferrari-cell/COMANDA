<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  // Notificaciones removido: placeholder sin uso. Se reactiva cuando
  // haya una implementación real (push a cocina, mesero, etc.).
  const tabs = computed(() => [
    { label: t('admin::sidebar.seating_plan'), to: { name: 'admin.salon.plano' } },
    { label: t('admin::sidebar.sales_channels'), to: { name: 'admin.salon.canales' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
