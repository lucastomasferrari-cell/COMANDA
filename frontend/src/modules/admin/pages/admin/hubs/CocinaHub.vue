<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  // Tabs del hub Cocina. "KDS" absorbe la config de cocina (el tab
  // separado "Ajustes" con solo el checkbox de auto-refresh quedaba
  // redundante — la configuracion pertenece al mismo dominio que el
  // display de cocina).
  const tabs = computed(() => [
    { label: t('admin::sidebar.printers'), to: { name: 'admin.cocina.impresoras' } },
    { label: t('admin::sidebar.kds'), to: { name: 'admin.cocina.kds' } },
    { label: t('admin::sidebar.print_rules'), to: { name: 'admin.cocina.reglas' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
