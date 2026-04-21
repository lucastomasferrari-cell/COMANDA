<script lang="ts" setup>
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'

  const { t } = useI18n()
  const { user } = useAuth()

  const headers: TableHeader[] = [
    { title: t('giftcard::gift_card_batches.table.name'), value: 'name', sortable: false },
    { title: t('giftcard::gift_card_batches.table.prefix'), value: 'prefix', sortable: true },
    { title: t('giftcard::gift_card_batches.table.quantity'), value: 'quantity', sortable: true },
    { title: t('giftcard::gift_card_batches.table.value'), value: 'value', sortable: true },
    {
      title: t('giftcard::gift_card_batches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    {
      title: t('giftcard::gift_card_batches.table.cards_generated'),
      value: 'cards_generated',
      sortable: false,
    },
    { title: t('giftcard::gift_card_batches.table.cards_used'), value: 'cards_used', sortable: false },
    {
      title: t('giftcard::gift_card_batches.table.cards_remaining'),
      value: 'cards_remaining',
      sortable: false,
    },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
  ]

  const actions: TableAction[] = [
    {
      key: 'destroy',
      confirm: {
        message: t('admin::admin.delete.confirmation_message'),
        confirmButtonText: t('admin::admin.delete.confirm_button_text'),
      },
    },
  ]

  const cellComponents: Record<string, any> = {
    value: {
      component: TableMoney,
      props: { column: 'value' },
    },
  }
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/gift-card-batches"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="giftcard"
    name="gift_card_batch"
    resource="gift_card_batches"
  />
</template>
