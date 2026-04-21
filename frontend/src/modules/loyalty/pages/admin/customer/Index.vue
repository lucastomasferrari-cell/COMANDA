<script lang="ts" setup>

  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    {
      title: t('loyalty::loyalty_customers.table.customer'),
      value: 'customer.name',
      sortable_key: 'customer_id',
      sortable: true,
    },
    {
      title: t('loyalty::loyalty_customers.table.loyalty_program'),
      value: 'loyalty_program.name',
      sortable_key: 'loyalty_program_id',
      sortable: true,
    },
    {
      title: t('loyalty::loyalty_customers.table.loyalty_tier'),
      value: 'loyalty_tier.name',
      sortable_key: 'loyalty_tier_id',
      sortable: true,
    },
    { title: t('loyalty::loyalty_customers.table.points_balance'), value: 'points_balance', sortable: true },
    { title: t('loyalty::loyalty_customers.table.lifetime_points'), value: 'lifetime_points', sortable: true },
    { title: t('loyalty::loyalty_customers.table.last_earned_at'), value: 'last_earned_at', sortable: true },
    { title: t('loyalty::loyalty_customers.table.last_redeemed_at'), value: 'last_redeemed_at', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
  ]

</script>

<template>
  <SmartDataTable
    api-uri="/v1/loyalty-customers"
    :headers="headers"
    module="loyalty"
    name="loyalty_customer"
    resource="loyalty_customers"
  />
</template>
