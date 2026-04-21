<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    { title: t('order::reasons.table.name'), value: 'name', sortable: true },
    { title: t('order::reasons.table.type'), value: 'type', sortable: true },
    { title: t('admin::admin.table.activation'), value: 'is_active', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
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
    type: {
      component: TableEnum,
      props: {
        column: 'type',
      },
    },
  }
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/reasons"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="order"
    name="reason"
    resource="reasons"
  />
</template>
