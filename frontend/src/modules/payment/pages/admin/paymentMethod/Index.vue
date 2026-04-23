<script lang="ts" setup>
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import TableBadge from './Partials/TableBadge.vue'
  import TableType from './Partials/TableType.vue'

  const { t } = useI18n()
  const router = useRouter()

  const headers: TableHeader[] = [
    { title: t('payment::payment_methods.table.name'), value: 'name', sortable: true },
    { title: t('payment::payment_methods.table.type'), value: 'type', sortable: true, sortable_key: 'type' },
    { title: t('payment::payment_methods.table.impacts_cash'), value: 'impacts_cash', sortable: false },
    { title: t('admin::admin.table.status'), value: 'is_active', sortable: true },
    { title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true },
  ]

  // onClick override en edit + create: por default el SmartDataTable resuelve
  // admin.${resource}.${action.key} (= admin.payment_methods.create) que es la
  // ruta standalone del vendor. Queremos que use la URL anidada bajo el sub-hub
  // Configuración > Operación para que las tabs queden visibles durante el CRUD.
  const actions: TableAction[] = [
    {
      key: 'edit',
      onClick: item => {
        router.push({ name: 'admin.configuracion.operacion.formas.edit', params: { id: item.id } })
      },
    },
    {
      key: 'destroy',
      confirm: {
        message: t('admin::admin.delete.confirmation_message'),
        confirmButtonText: t('admin::admin.delete.confirm_button_text'),
      },
    },
  ]

  const headerActions: TableAction[] = [
    {
      key: 'create',
      onClick: () => {
        router.push({ name: 'admin.configuracion.operacion.formas.create' })
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
    type: { component: TableType },
    impacts_cash: { component: TableBadge },
  }
</script>

<template>
  <SmartDataTable
    :actions="actions"
    api-uri="/v1/payment-methods"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="headerActions"
    :headers="headers"
    module="payment"
    name="payment_method"
    resource="payment_methods"
  />
</template>
