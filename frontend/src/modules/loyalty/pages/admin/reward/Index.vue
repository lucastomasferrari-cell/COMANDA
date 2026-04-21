<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    { title: t('loyalty::loyalty_rewards.table.icon'), value: 'icon', sortable: false },
    { title: t('loyalty::loyalty_rewards.table.name'), value: 'name', sortable: true },
    {
      title: t('loyalty::loyalty_rewards.table.loyalty_program'),
      value: 'loyalty_program.name',
      sortable_key: 'loyalty_program_id',
      sortable: true,
    },
    { title: t('loyalty::loyalty_rewards.table.type'), value: 'type', sortable: true },
    { title: t('loyalty::loyalty_rewards.table.points_cost'), value: 'points_cost', sortable: true },
    { title: t('loyalty::loyalty_rewards.table.total_redeemed'), value: 'total_redeemed', sortable: true },
    { title: t('loyalty::loyalty_rewards.table.total_customers'), value: 'total_customers', sortable: true },
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
    api-uri="/v1/loyalty-rewards"
    :bulk-actions="bulkActions"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="loyalty"
    name="loyalty_reward"
    resource="loyalty_rewards"
  />
</template>
