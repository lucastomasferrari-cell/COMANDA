<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const { t } = useI18n()
  const { user } = useAuth()

  const headers: TableHeader[] = [
    { title: t('pos::pos_registers.table.name'), value: 'name', sortable: true },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    { title: t('pos::pos_registers.table.code'), value: 'code', sortable: true },
    {
      title: t('pos::pos_registers.table.invoice_printer'),
      value: 'invoice_printer.name',
      sortable_key: 'invoice_printer_id',
      sortable: true,
    },
    {
      title: t('pos::pos_registers.table.bill_printer'),
      value: 'bill_printer.name',
      sortable_key: 'bill_printer_id',
      sortable: true,
    },
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

</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/pos/registers"
    :bulk-actions="bulkActions"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="pos"
    name="pos_register"
    resource="pos_registers"
  />
</template>
