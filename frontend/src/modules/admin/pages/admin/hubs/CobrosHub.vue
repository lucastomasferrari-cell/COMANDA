<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  const tabs = computed(() => [
    { label: t('admin::sidebar.payment_methods'), to: { name: 'admin.cobros.formas' } },
    { label: t('admin::sidebar.taxes'), to: { name: 'admin.cobros.impuestos' } },
    { label: t('admin::sidebar.reasons'), to: { name: 'admin.cobros.motivos' } },
    { label: t('admin::sidebar.pos_registers'), to: { name: 'admin.cobros.caja' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
