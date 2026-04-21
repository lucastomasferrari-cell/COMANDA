<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    { title: t('loyalty::loyalty_tiers.table.icon'), value: 'icon', sortable: false },
    { title: t('loyalty::loyalty_tiers.table.name'), value: 'name', sortable: true },
    {
      title: t('loyalty::loyalty_tiers.table.program'),
      value: 'loyalty_program.name',
      sortable_key: 'loyalty_program_id',
      sortable: true,
    },
    { title: t('loyalty::loyalty_tiers.table.min_spend'), value: 'min_spend', sortable: true },
    { title: t('loyalty::loyalty_tiers.table.multiplier'), value: 'multiplier', sortable: true },
    { title: t('admin::admin.table.status'), value: 'is_active', sortable: false },
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

  const cellComponents: Record<string, any> = {
    min_spend: {
      component: TableMoney,
      props: { column: 'min_spend' },
    },
  }

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
    api-uri="/v1/loyalty-tiers"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="loyalty"
    name="loyalty_tier"
    resource="loyalty_tiers"
  />
</template>
