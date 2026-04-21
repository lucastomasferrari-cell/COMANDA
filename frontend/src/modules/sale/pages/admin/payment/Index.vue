<script lang="ts" setup>

  import { useI18n } from 'vue-i18n'
  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'

  const { t } = useI18n()
  const { user } = useAuth()

  const headers: TableHeader[] = [
    {
      title: t('payment::payments.table.order_reference_no'),
      value: 'order_reference_no',
      sortable: true,
    },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    {
      title: t('payment::payments.table.cashier'),
      value: 'cashier.name',
      sortable_key: 'cashier_id',
      sortable: true,
    },
    { title: t('payment::payments.table.method'), value: 'method', sortable: true },
    { title: t('payment::payments.table.amount'), value: 'amount', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
  ]

  const cellComponents: Record<string, any> = {
    method: {
      component: TableEnum,
      props: { column: 'method' },
    },
  }

</script>

<template>
  <SmartDataTable
    api-uri="/v1/payments"
    :cell-components="cellComponents"
    :headers="headers"
    module="payment"
    name="payment"
    resource="payments"
  />
</template>
