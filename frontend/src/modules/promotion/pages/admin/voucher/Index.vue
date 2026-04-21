<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const { t } = useI18n()
  const { user } = useAuth()

  const headers: TableHeader[] = [
    { title: t('voucher::vouchers.table.name'), value: 'name', sortable: true },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    { title: t('voucher::vouchers.table.code'), value: 'code', sortable: true },
    { title: t('voucher::vouchers.table.type'), value: 'type', sortable: true },
    { title: t('voucher::vouchers.table.value_text'), value: 'value_text', sortable_key: 'value', sortable: true },
    { title: t('voucher::vouchers.table.used'), value: 'used', sortable: true },
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
    role: {
      component: TableEnum,
      props: { column: 'role' },
    },
    connection_type: {
      component: TableEnum,
      props: { column: 'connection_type' },
    },
  }
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/vouchers"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="voucher"
    name="voucher"
    resource="vouchers"
  />
</template>
