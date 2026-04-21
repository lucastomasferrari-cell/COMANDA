<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import TableQrCode from './Partials/TableQrCode.vue'

  const { t } = useI18n()
  const { user } = useAuth()

  const headers: TableHeader[] = [
    { title: t('seatingplan::tables.table_columns.name'), value: 'name', sortable: true },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    {
      title: t('seatingplan::tables.table_columns.floor'),
      value: 'floor.name',
      sortable_key: 'floor_id',
      sortable: true,
    },
    {
      title: t('seatingplan::tables.table_columns.zone'),
      value: 'zone.name',
      sortable_key: 'zone_id',
      sortable: true,
    },
    { title: t('seatingplan::tables.table_columns.capacity'), value: 'capacity', sortable: true },
    { title: t('seatingplan::tables.table_columns.status'), value: 'status', sortable: true },
    { title: t('seatingplan::tables.table_columns.qrcode'), value: 'qrcode', sortable: false },
    { title: t('admin::admin.table.activation'), value: 'is_active', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
    { title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true },
  ]

  const actions: TableAction[] = [
    { key: 'show' },
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
    qrcode: {
      component: TableQrCode,
    },
  }
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/tables"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="seatingplan"
    name="table"
    resource="tables"
  />
</template>
