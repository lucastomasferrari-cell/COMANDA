<script lang="ts" setup>

  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    {
      title: t('loyalty::loyalty_transactions.table.customer'),
      value: 'customer.name',
      sortable_key: 'customer_id',
      sortable: true,
    },
    {
      title: t('loyalty::loyalty_transactions.table.description'),
      value: 'description',
      sortable: false,
    },
    {
      title: t('loyalty::loyalty_transactions.table.type'),
      value: 'type',
      sortable: true,
    },
    { title: t('loyalty::loyalty_transactions.table.points'), value: 'points', sortable: true },
    { title: t('loyalty::loyalty_transactions.table.amount'), value: 'amount', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
  ]

</script>

<template>
  <SmartDataTable
    api-uri="/v1/loyalty-transactions"
    :headers="headers"
    module="loyalty"
    name="loyalty_transaction"
    resource="loyalty_transactions"
  />
</template>
