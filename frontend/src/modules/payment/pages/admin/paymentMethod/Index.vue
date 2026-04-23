<script lang="ts" setup>
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import TableBadge from './Partials/TableBadge.vue'
  import TableType from './Partials/TableType.vue'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    { title: t('payment::payment_methods.table.name'), value: 'name', sortable: true },
    { title: t('payment::payment_methods.table.type'), value: 'type', sortable: true, sortable_key: 'type' },
    { title: t('payment::payment_methods.table.impacts_cash'), value: 'impacts_cash', sortable: false },
    { title: t('admin::admin.table.status'), value: 'is_active', sortable: true },
    { title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true },
  ]

  const actions: TableAction[] = [
    { key: 'edit' },
    {
      key: 'destroy',
      confirm: {
        message: t('admin::admin.delete.confirmation_message'),
        confirmButtonText: t('admin::admin.delete.confirm_button_text'),
      },
    },
  ]

  const bulkActions: TableAction[] = [
    {
      key: 'destroy',
      confirm: {
        message: t('admin::admin.delete.confirmation_message'),
        confirmButtonText: t('admin::admin.delete.confirm_button_text'),
      },
    },
  ]

  const cellComponents: Record<string, any> = {
    type: { component: TableType },
    impacts_cash: { component: TableBadge },
  }
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/payment-methods"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="payment"
    name="payment_method"
    resource="payment_methods"
  />
</template>
