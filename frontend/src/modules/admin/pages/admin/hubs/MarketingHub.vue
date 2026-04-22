<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageTabs from '@/modules/core/components/PageTabs.vue'

  const { t } = useI18n()

  const tabs = computed(() => [
    { label: t('admin::sidebar.discounts'), to: { name: 'admin.marketing.descuentos' } },
    { label: t('admin::sidebar.vouchers'), to: { name: 'admin.marketing.cupones' } },
    { label: t('admin::sidebar.gift_cards'), to: { name: 'admin.marketing.gift_cards' } },
    { label: t('admin::sidebar.loyalty'), to: { name: 'admin.marketing.fidelizacion' } },
    { label: t('admin::sidebar.customers'), to: { name: 'admin.marketing.clientes' } },
  ])
</script>

<template>
  <PageTabs :tabs="tabs" />
</template>
