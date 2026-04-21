<script lang="ts" setup>

import type {TableAction, TableHeader} from '@/modules/core/contracts/Table.ts'
import {useI18n} from 'vue-i18n'
import {useAuth} from '@/modules/auth/composables/auth.ts'
import TableName from './Partials/TableName.vue'

const {t} = useI18n()
const {user} = useAuth()

const headers: TableHeader[] = [
  {title: t('user::users.table.name'), value: 'name', sortable: true},
  {
    title: t('branch::branches.table.branch'),
    value: 'branch.name',
    sortable_key: 'branch_id',
    sortable: true,
    hidden: user?.assigned_to_branch,
  },
  {title: t('user::users.table.email'), value: 'email', sortable: true},
  {title: t('admin::admin.table.status'), value: 'is_active', sortable: false},
  {title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true},
  {title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true},
]

const actions: TableAction[] = [
  {key: 'edit'},
  {
    key: 'destroy',
    hidden: item => item.is_main_user,
    confirm: {
      message: t('admin::admin.delete.confirmation_message'),
      confirmButtonText: t('admin::admin.delete.confirm_button_text'),
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
      :bulk-actions="bulkActions"
      :cell-components="cellComponents"
      :header-actions="[{ key: 'create' }]"
      :headers="headers"
      api-uri="/v1/users"
      module="user"
      name="user"
      resource="users"
  />
</template>
