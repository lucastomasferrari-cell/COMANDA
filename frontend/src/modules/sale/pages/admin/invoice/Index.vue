<script lang="ts" setup>

  import type { RouteLocationRaw } from 'vue-router'
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'
  import { useInvoice } from '@/modules/sale/composables/invoice.ts'

  const { t } = useI18n()
  const { user, can } = useAuth()
  const router = useRouter()
  const { print, download } = useInvoice()

  const headers: TableHeader[] = [
    { title: t('invoice::invoices.table.invoice_number'), value: 'invoice_number', sortable: true },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable: true,
      sortable_key: 'branch_id',
      hidden: user?.assigned_to_branch,
    },
    { title: t('invoice::invoices.table.seller'), value: 'seller.name', sortable: false },
    { title: t('invoice::invoices.table.buyer'), value: 'buyer.name', sortable: false },
    { title: t('invoice::invoices.table.type'), value: 'type', sortable: true },
    { title: t('invoice::invoices.table.status'), value: 'status', sortable: true },
    { title: t('invoice::invoices.table.purpose'), value: 'purpose', sortable: true },
    { title: t('invoice::invoices.table.invoice_kind'), value: 'invoice_kind', sortable: true },
    { title: t('invoice::invoices.table.total'), value: 'total', sortable: true },
    { title: t('invoice::invoices.table.issued_at'), value: 'issued_at', sortable: true },
  ]

  const cellComponents: Record<string, any> = {
    purpose: {
      component: TableEnum,
      props: { column: 'purpose' },
    },
    invoice_kind: {
      component: TableEnum,
      props: { column: 'invoice_kind' },
    },
  }

  function onRowClick (event: MouseEvent, value: any) {
    if (can('admin.invoices.show')) {
      router.push({
        name: 'admin.invoices.show',
        params: { id: value.item.uuid },
      } as unknown as RouteLocationRaw)
    }
  }

  const actions: TableAction[] = [
    { key: 'show' },
    {
      key: 'print',
      onClick: (item: Record<string, any>) => print(item),
      icon: 'tabler-printer',
    },
    {
      key: 'download',
      onClick: (item: Record<string, any>) => download(item),
      icon: 'tabler-download',
    },
  ]
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/invoices"
    :cell-components="cellComponents"
    :headers="headers"
    module="invoice"
    name="invoice"
    :on-row-click="can('admin.invoices.show')?onRowClick:null"
    resource="invoices"
  />
</template>
