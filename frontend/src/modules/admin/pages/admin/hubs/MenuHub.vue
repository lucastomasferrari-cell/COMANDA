<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  // Orden de flujo de creación: primero la estructura (Menús), después
  // las categorías que organizan, después las opciones (addons reusables)
  // y finalmente los productos que consumen todo lo anterior. El user
  // que entra por primera vez al hub sigue el pipeline natural.
  const tabs = computed(() => [
    { label: t('admin::sidebar.menus'), to: { name: 'admin.menu.menus' } },
    { label: t('admin::sidebar.categories'), to: { name: 'admin.menu.categorias' } },
    { label: t('admin::sidebar.options'), to: { name: 'admin.menu.opciones' } },
    { label: t('admin::sidebar.products'), to: { name: 'admin.menu.productos' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
