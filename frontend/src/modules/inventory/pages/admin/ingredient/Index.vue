<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import TableCurrentStock from './Partials/TableCurrentStock.vue'

  const { t } = useI18n()
  const { user } = useAuth()

  const headers: TableHeader[] = [
    { title: t('inventory::ingredients.table.name'), value: 'name', sortable: true },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    { title: t('inventory::ingredients.table.current_stock'), value: 'current_stock', sortable: true },
    { title: t('inventory::ingredients.table.alert_quantity'), value: 'alert_quantity', sortable: true },
    { title: t('inventory::ingredients.table.unit'), value: 'unit_name', sortable: false },
    {
      title: t('inventory::ingredients.table.cost_per_unit'),
      value: 'cost_per_unit.formatted',
      sortable_key: 'cost_per_unit',
      sortable: true,
    },
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
    current_stock: {
      component: TableCurrentStock,
    },
  }
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/ingredients"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="inventory"
    name="ingredient"
    resource="ingredients"
  />
</template>
