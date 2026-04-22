<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  const tabs = computed(() => [
    { label: t('admin::sidebar.products'), to: { name: 'admin.menu.productos' } },
    { label: t('admin::sidebar.categories'), to: { name: 'admin.menu.categorias' } },
    { label: t('admin::sidebar.options'), to: { name: 'admin.menu.opciones' } },
    { label: t('admin::sidebar.menus'), to: { name: 'admin.menu.menus' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
