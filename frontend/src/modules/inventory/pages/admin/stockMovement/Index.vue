<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { defineAsyncComponent } from 'vue'
  import TableType from './Partials/TableType.vue'
  const ShowDialog = defineAsyncComponent(() => import('./Show.vue'))

  const { t } = useI18n()
  const { user, can } = useAuth()

  const showDetailsDialog = ref(false)
  const itemId = ref<number | null>(null)

  const showDetails = (item: any) => {
    if (can('admin.stock_movements.show')) {
      itemId.value = item.id
      showDetailsDialog.value = true
    }
  }

  const headers: TableHeader[] = [
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    {
      title: t('inventory::stock_movements.table.ingredient_name'),
      value: 'ingredient.name',
      sortable_key: 'ingredient_id',
      sortable: true,
    },
    { title: t('inventory::stock_movements.table.type'), value: 'type', sortable: true },
    { title: t('inventory::stock_movements.table.quantity'), value: 'quantity', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
    { title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true },
  ]

  const actions: TableAction[] = [
    {
      key: 'show',
      onClick: showDetails,
    },
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
      component: TableType,
    },
  }

</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/stock-movements"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :has-search="false"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="inventory"
    name="stock_movement"
    resource="stock_movements"
  />
  <ShowDialog
    v-if=" can('admin.stock_movements.show') && showDetailsDialog && itemId"
    :id="itemId"
    v-model="showDetailsDialog"
  />
</template>
