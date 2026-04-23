<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  // Sub-hub "Operación" = Formas de cobro + Impresión + KDS.
  // Formas de cobro migró desde /admin/cobros/formas; las rutas viejas
  // redirigen acá. Impresión absorbe impresoras + reglas. KDS es el
  // panel de settings (auto-refresh mode/interval) — el viewer full
  // sigue en /admin/kds.
  const tabs = computed(() => [
    { label: t('admin::sidebar.payment_methods'), to: { name: 'admin.configuracion.operacion.formas' } },
    { label: t('admin::sidebar.printing'), to: { name: 'admin.configuracion.operacion.impresion' } },
    { label: t('admin::sidebar.kds'), to: { name: 'admin.configuracion.operacion.kds' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
