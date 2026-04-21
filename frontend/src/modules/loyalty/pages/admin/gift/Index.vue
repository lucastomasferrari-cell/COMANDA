<script lang="ts" setup>

  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    {
      title: t('loyalty::loyalty_gifts.table.customer'),
      value: 'customer.name',
      sortable: false,
    },
    {
      title: t('loyalty::loyalty_gifts.table.program'),
      value: 'program.name',
      sortable_key: 'loyalty_program_id',
      sortable: true,
    },
    {
      title: t('loyalty::loyalty_gifts.table.reward'),
      value: 'reward.name',
      sortable_key: 'loyalty_reward_id',
      sortable: true,
    },
    { title: t('loyalty::loyalty_gifts.table.status'), value: 'status', sortable: true },
    { title: t('loyalty::loyalty_gifts.table.type'), value: 'type', sortable: true },
    { title: t('loyalty::loyalty_gifts.table.points_spent'), value: 'points_spent', sortable: true },
    { title: t('loyalty::loyalty_gifts.table.valid_until'), value: 'valid_until', sortable: true },
    { title: t('loyalty::loyalty_gifts.table.used_at'), value: 'used_at', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
  ]
</script>

<template>
  <SmartDataTable
    api-uri="/v1/loyalty-gifts"
    :has-search="false"
    :headers="headers"
    module="loyalty"
    name="loyalty_gift"
    resource="loyalty_gifts"
  />
</template>
