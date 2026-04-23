<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  // Sub-hub "Plano de mesas" — movido desde el hub Salón eliminado en
  // el sprint 5 cards. El plano integral (Floors+Zones+Tables stackeados)
  // queda como tab default; Zonas es el listado CRUD de seating zones,
  // que era su propio Index.vue standalone antes.
  const tabs = computed(() => [
    { label: t('admin::sidebar.plano_visual'), to: { name: 'admin.configuracion.plano_de_mesas.visual' } },
    { label: t('admin::sidebar.zonas'), to: { name: 'admin.configuracion.plano_de_mesas.zonas' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
