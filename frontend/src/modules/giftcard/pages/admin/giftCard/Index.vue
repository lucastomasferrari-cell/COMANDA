<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'

  const { t } = useI18n()
  const { user, can } = useAuth()
  const router = useRouter()

  const headers: TableHeader[] = [
    { title: t('giftcard::gift_cards.table.code'), value: 'code', sortable: true },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    {
      title: t('giftcard::gift_cards.table.customer'),
      value: 'customer.name',
      sortable_key: 'customer_id',
      sortable: true,
    },
    { title: t('giftcard::gift_cards.table.status'), value: 'status', sortable: true },
    {
      title: t('giftcard::gift_cards.table.initial_balance'),
      value: 'initial_balance',
      sortable: true,
    },
    {
      title: t('giftcard::gift_cards.table.current_balance'),
      value: 'current_balance',
      sortable: true,
    },
    { title: t('giftcard::gift_cards.table.expiry_date'), value: 'expiry_date', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
  ]

  const actions: TableAction[] = [
    { key: 'show' },
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
    initial_balance: {
      component: TableMoney,
      props: { column: 'initial_balance' },
    },
    current_balance: {
      component: TableMoney,
      props: { column: 'current_balance' },
    },
    scope: {
      component: TableEnum,
      props: { column: 'scope' },
    },
  }

  function onRowClick (event: MouseEvent, value: any) {
    if (can('admin.gift_cards.show')) {
      void router.push({
        name: 'admin.gift_cards.show',
        params: { id: value.item.id },
      } as unknown as RouteLocationRaw)
    }
  }
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/gift-cards"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[
      { key: 'create' },
      {
        key: 'create',
        permission: 'admin.gift_card_batches.create',
        label: t('giftcard::gift_card_batches.gift_card_batch'),
        onClick: () => void router.push({ name: 'admin.gift_card_batches.create' })
      },
    ]"
    :headers="headers"
    module="giftcard"
    name="gift_card"
    :on-row-click="can('admin.gift_cards.show') ? onRowClick : null"
    resource="gift_cards"
  />
</template>
