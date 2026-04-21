<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import TableCompound from './Partials/TableCompound.vue'
  import TableName from './Partials/TableName.vue'
  import TableType from './Partials/TableType.vue'

  const { t } = useI18n()
  const { user } = useAuth()

  const headers: TableHeader[] = [
    { title: t('tax::taxes.table.name'), value: 'name', sortable: true },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable: true,
      sortable_key: 'branch_id',
      hidden: user?.assigned_to_branch,
    },
    { title: t('tax::taxes.table.code'), value: 'code', sortable: true },
    { title: t('tax::taxes.table.rate'), value: 'rate', sortable: true },
    { title: t('tax::taxes.table.compound'), value: 'compound', sortable: true },
    { title: t('tax::taxes.table.type'), value: 'type', sortable: true },
    { title: t('admin::admin.table.status'), value: 'is_active', sortable: true },
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
    name: {
      component: TableName,
    },
    compound: {
      component: TableCompound,
    },
    type: {
      component: TableType,
    },
  }
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/taxes"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="tax"
    name="tax"
    resource="taxes"
  />
</template>
