<script lang="ts" setup>

  import type { RouteLocationRaw } from 'vue-router'
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'
  import OrderPrintDialog from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/OrderPrint/Index.vue'
  import RefundCancelDialog from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/RefundCancelDialog.vue'
  import TableCustomer from '@/modules/sale/pages/admin/order/Partials/TableCustomer.vue'
  import TableReferenceNo from '@/modules/sale/pages/admin/order/Partials/TableReferenceNo.vue'

  const { t } = useI18n()
  const { user, can } = useAuth()
  const router = useRouter()

  const smartTableRef = ref()
  const refundCancelDialog = ref<Record<string, any>>({ orderId: null, open: false })
  const printDialog = ref<Record<string, any>>({ orderId: null, open: false })

  function refundCancel (action: string, orderId: number | string) {
    if (can(`admin.orders.${action}`)) {
      refundCancelDialog.value.orderId = orderId
      refundCancelDialog.value.open = true
    }
  }

  function showPrintDialog (orderId: number | string) {
    if (can('admin.orders.print')) {
      printDialog.value.orderId = orderId
      printDialog.value.open = true
    }
  }

  const headers: TableHeader[] = [
    {
      title: t('order::orders.table.customer'),
      value: 'customer',
      sortable: true,
      sortable_key: 'customer_id',
    },
    { title: t('order::orders.table.reference_no'), value: 'reference_no', sortable: true },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable: true,
      sortable_key: 'branch_id',
      hidden: user?.assigned_to_branch,
    },
    { title: t('order::orders.table.type'), value: 'type', sortable: true },
    { title: t('order::orders.table.status'), value: 'status', sortable: true },
    { title: t('order::orders.table.payment_status'), value: 'payment_status', sortable: true },
    { title: t('order::orders.table.total'), value: 'total', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
  ]

  const actions: TableAction[] = [
    { key: 'show' },
    {
      key: 'print',
      onClick: (item: Record<string, any>) => showPrintDialog(item.id),
      icon: 'tabler-printer',
      hidden: item => ['cancelled', 'refunded', 'merged'].includes(item.status.id),
    },
    {
      key: 'cancel',
      onClick: (item: Record<string, any>) => refundCancel('cancel', item.id),
      icon: 'tabler-x',
      color: 'error',
      hidden: (item: Record<string, any>) => !item.allow_cancel,
    },
    {
      key: 'refund',
      onClick: (item: Record<string, any>) => refundCancel('refund', item.id),
      icon: 'tabler-arrow-back-up',
      color: 'error',
      hidden: (item: Record<string, any>) => !item.allow_refund,
    },
  ]

  function onRowClick (event: MouseEvent, value: any) {
    if (can('admin.orders.show')) {
      router.push({
        name: 'admin.orders.show',
        params: { id: value.item.reference_no },
      } as unknown as RouteLocationRaw)
    }
  }

  const cellComponents: Record<string, any> = {
    reference_no: {
      component: TableReferenceNo,
    },
    customer: {
      component: TableCustomer,
    },
    type: {
      component: TableEnum,
      props: { column: 'type' },
    },
    payment_status: {
      component: TableEnum,
      props: { column: 'payment_status' },
    },
  }

  function resolvedRefundCancel () {
    smartTableRef.value.refresh()
  }

</script>

<template>
  <SmartDataTable
    ref="smartTableRef"
    :actions="actions"
    api-uri="/v1/orders"
    :cell-components="cellComponents"
    :headers="headers"
    module="order"
    name="order"
    :on-row-click="can('admin.orders.show')?onRowClick:null"
    resource="orders"
  />
  <RefundCancelDialog
    v-if="(can('admin.orders.cancel')||can('admin.orders.refund')) && refundCancelDialog.orderId != null && refundCancelDialog.open"
    v-model="refundCancelDialog.open"
    :order-id="refundCancelDialog.orderId"
    @resolved="resolvedRefundCancel"
  />

  <OrderPrintDialog
    v-if="can('admin.orders.print') && printDialog.orderId != null && printDialog.open"
    v-model="printDialog.open"
    :order-id="printDialog.orderId"
  />
</template>
