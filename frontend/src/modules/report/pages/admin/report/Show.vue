<script lang="ts" setup>
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'

  const route = useRoute()
  const reportKey = (route.params as Record<string, any>).key
  const cellComponents = {
    tax: {
      component: TableMoney,
      props: { column: 'tax' },
    },
    subtotal: {
      component: TableMoney,
      props: { column: 'subtotal' },
    },
    system_cash_sales: {
      component: TableMoney,
      props: { column: 'system_cash_sales' },
    },
    initial_balance: {
      component: TableMoney,
      props: { column: 'initial_balance' },
    },
    amount_redeemed: {
      component: TableMoney,
      props: { column: 'amount_redeemed' },
    },
    current_balance: {
      component: TableMoney,
      props: { column: 'current_balance' },
    },
    total_sold_value: {
      component: TableMoney,
      props: { column: 'total_sold_value' },
    },
    redeemed_value: {
      component: TableMoney,
      props: { column: 'redeemed_value' },
    },
    outstanding_balance: {
      component: TableMoney,
      props: { column: 'outstanding_balance' },
    },
    amount_converted: {
      component: TableMoney,
      props: { column: 'amount_converted' },
    },
    expired_balance: {
      component: TableMoney,
      props: { column: 'expired_balance' },
    },
    remaining_balance: {
      component: TableMoney,
      props: { column: 'remaining_balance' },
    },
    balance_before: {
      component: TableMoney,
      props: { column: 'balance_before' },
    },
    balance_after: {
      component: TableMoney,
      props: { column: 'balance_after' },
    },
    total_value: {
      component: TableMoney,
      props: { column: 'total_value' },
    },
    system_card_sales: {
      component: TableMoney,
      props: { column: 'system_card_sales' },
    },
    system_other_sales: {
      component: TableMoney,
      props: { column: 'system_other_sales' },
    },
    total_sales: {
      component: TableMoney,
      props: { column: 'total_sales' },
    },
    total_refunds: {
      component: TableMoney,
      props: { column: 'total_refunds' },
    },
    total_cost: {
      component: TableMoney,
      props: { column: 'total_cost' },
    },
    revenue: {
      component: TableMoney,
      props: { column: 'revenue' },
    },
    total_discount: {
      component: TableMoney,
      props: { column: 'total_discount' },
    },
    average_order_value: {
      component: TableMoney,
      props: { column: 'average_order_value' },
    },
    revenue_before: {
      component: TableMoney,
      props: { column: 'revenue_before' },
    },
    revenue_after: {
      component: TableMoney,
      props: { column: 'revenue_after' },
    },
    cost_price: {
      component: TableMoney,
      props: { column: 'cost_price' },
    },
    loyalty_cost: {
      component: TableMoney,
      props: { column: 'loyalty_cost' },
    },
    net_impact: {
      component: TableMoney,
      props: { column: 'net_impact' },
    },
  }
</script>

<template>
  <SmartDataTable
    :api-uri="`/v1/reports/${reportKey}`"
    :cell-components="cellComponents"
    :has-search="false"
    module="report"
    name="report"
    resource="reports"
  />
</template>
