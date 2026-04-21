<script lang="ts" setup>

  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import TableContact from './Partials/TableContact.vue'
  import TableLocation from './Partials/TableLocation.vue'
  import TableRegionalInfo from './Partials/TableRegionalInfo.vue'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    { title: t('admin::admin.table.id'), value: 'id', sortable: false },
    { title: t('branch::branches.table.name'), value: 'name', sortable: true },
    { title: t('branch::branches.table.contact'), value: 'contact', sortable: false },
    { title: t('branch::branches.table.regional_info'), value: 'regional_info', sortable: false },
    { title: t('branch::branches.table.location'), value: 'location', sortable: false },
    { title: t('admin::admin.table.status'), value: 'is_active', sortable: false },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
    { title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true },
  ]

  const actions: TableAction[] = [
    { key: 'edit' },
    {
      key: 'destroy',
      hidden: item => item.is_main,
      confirm: {
        message: t('admin::admin.delete.confirmation_message'),
        confirmButtonText: t('admin::admin.delete.confirm_button_text'),
      },
    },
  ]

  const cellComponents: Record<string, any> = {
    contact: {
      component: TableContact,
    },
    location: {
      component: TableLocation,
    },
    regional_info: {
      component: TableRegionalInfo,
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
    api-uri="/v1/branches"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="branch"
    name="branch"
    resource="branches"
  />
</template>
