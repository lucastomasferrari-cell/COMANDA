<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import TableName from './Partials/TableName.vue'

  const { t } = useI18n()
  const router = useRouter()

  const headers: TableHeader[] = [
    { title: t('user::customers.table.name'), value: 'name', sortable: true },
    { title: t('user::customers.table.phone'), value: 'phone', sortable_key: 'phone', sortable: true },
    { title: t('admin::admin.table.status'), value: 'is_active', sortable: false },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
    { title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true },
  ]

  // Override edit + create para apuntar a /admin/clientes/* en vez de la
  // ruta vendor /admin/customers/* (el módulo user sigue exportando esa, pero
  // el flujo admin vive bajo clientes top-level desde el sprint 4 cards).
  const actions: TableAction[] = [
    {
      key: 'edit',
      onClick: item => {
        router.push({ name: 'admin.clientes.edit', params: { id: item.id } })
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
        router.push({ name: 'admin.clientes.create' })
      },
    },
  ]

  const cellComponents: Record<string, any> = {
    name: {
      component: TableName,
    },
  }

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
    api-uri="/v1/customers"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="headerActions"
    :headers="headers"
    module="user"
    name="customer"
    resource="customers"
  />
</template>
