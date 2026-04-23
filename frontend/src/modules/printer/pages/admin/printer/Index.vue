<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const { t } = useI18n()
  const { user } = useAuth()
  const router = useRouter()

  const headers: TableHeader[] = [
    { title: t('printer::printers.table.name'), value: 'name', sortable: true },
    { title: t('printer::printers.table.qintrix_id'), value: 'qintrix_id', sortable: false },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
    sortable_key: 'branch_id',
      sortable: true,
      hidden: true,
    },
    { title: t('admin::admin.table.activation'), value: 'is_active', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
    { title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true },
  ]

  // Override edit + create para navegar a la URL anidada del sub-hub Operación.
  const actions: TableAction[] = [
    {
      key: 'edit',
      onClick: item => {
        router.push({ name: 'admin.configuracion.operacion.impresion.edit', params: { id: item.id } })
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
        router.push({ name: 'admin.configuracion.operacion.impresion.create' })
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
    api-uri="/v1/printers"
    :bulk-actions="bulkActions"
    :header-actions="headerActions"
    :headers="headers"
    module="printer"
    name="printer"
    resource="printers"
  />
</template>
