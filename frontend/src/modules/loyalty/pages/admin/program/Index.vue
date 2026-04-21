<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    { title: t('loyalty::loyalty_programs.table.name'), value: 'name', sortable: true },
    { title: t('loyalty::loyalty_programs.table.earning_rate'), value: 'earning_rate', sortable: true },
    // { title: t('loyalty::loyalty_programs.table.redemption_rate'), value: 'redemption_rate', sortable: true },
    // { title: t('loyalty::loyalty_programs.table.min_redeem_points'), value: 'min_redeem_points', sortable: true },
    { title: t('loyalty::loyalty_programs.table.points_expire_after'), value: 'points_expire_after', sortable: true },
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
    earning_rate: {
      component: TableMoney,
      props: { column: 'earning_rate' },
    },
    redemption_rate: {
      component: TableMoney,
      props: { column: 'redemption_rate' },
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
    api-uri="/v1/loyalty-programs"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="loyalty"
    name="loyalty_program"
    resource="loyalty_programs"
  />
</template>
