<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  // Post-Fix 8 bloque 4: solo "Formas de Pago" queda visible.
  // Impuestos → reactivar cuando integremos AFIP WSFE (IVA argentino).
  // Motivos → ya vive en Anti-fraude > Motivos de anulación.
  // Caja (pos_registers) → hardcoded a una sola row "CAJA" en el seed
  // del módulo POS, no editable por UI.
  // Las rutas admin.cobros.{impuestos,motivos,caja} siguen existiendo
  // para que el router no tire 404 si alguien las linkea directo, pero
  // no aparecen como tab ni en sidebar.
  const tabs = computed(() => [
    { label: t('admin::sidebar.payment_methods'), to: { name: 'admin.cobros.formas' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
