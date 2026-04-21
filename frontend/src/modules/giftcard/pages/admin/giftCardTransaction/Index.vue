<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'

  const { t } = useI18n()
  const { user, can } = useAuth()
  const router = useRouter()

  const headers: TableHeader[] = [
    {
      title: t('giftcard::gift_card_transactions.table.transaction_id'),
      value: 'uuid',
      sortable: true,
    },
    {
      title: t('giftcard::gift_card_transactions.table.gift_card'),
      value: 'gift_card.code',
      sortable: false,
    },
    { title: t('giftcard::gift_card_transactions.table.type'), value: 'type', sortable: true },
    { title: t('giftcard::gift_card_transactions.table.amount'), value: 'amount', sortable: true },
    {
      title: t('giftcard::gift_card_transactions.table.balance_before'),
      value: 'balance_before',
      sortable: true,
    },
    {
      title: t('giftcard::gift_card_transactions.table.balance_after'),
      value: 'balance_after',
      sortable: true,
    },
    {
      title: t('giftcard::gift_card_transactions.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    {
      title: t('giftcard::gift_card_transactions.table.user'),
      value: 'createdBy.name',
      sortable: false,
    },
    {
      title: t('giftcard::gift_card_transactions.table.transaction_at'),
      value: 'transaction_at',
      sortable: true,
    },
  ]
  const cellComponents: Record<string, any> = {
    amount: {
      component: TableMoney,
      props: { column: 'amount' },
    },
    balance_before: {
      component: TableMoney,
      props: { column: 'balance_before' },
    },
    balance_after: {
      component: TableMoney,
      props: { column: 'balance_after' },
    },
  }

  function onRowClick (event: MouseEvent, value: any) {
    if (can('admin.gift_card_transactions.show')) {
      router.push({
        name: 'admin.gift_card_transactions.show',
        params: { id: value.item.id },
      } as unknown as RouteLocationRaw)
    }
  }
</script>

<template>
  <SmartDataTable
    :actions="[{ key: 'show' }]"
    api-uri="/v1/gift-card-transactions"
    :cell-components="cellComponents"
    :headers="headers"
    module="giftcard"
    name="gift_card_transaction"
    :on-row-click="can('admin.gift_card_transactions.show') ? onRowClick : null"
    resource="gift_card_transactions"
  />
</template>
