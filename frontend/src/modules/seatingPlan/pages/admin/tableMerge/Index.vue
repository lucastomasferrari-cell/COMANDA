<script lang="ts" setup>

  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import TableMembers from '@/modules/seatingPlan/pages/admin/tableMerge/Partials/TableMembers.vue'

  const { t } = useI18n()
  const { user, can } = useAuth()

  const headers: TableHeader[] = [
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable: true,
      sortable_key: 'branch_id',
      hidden: user?.assigned_to_branch,
    },
    {
      title: t('admin::admin.table.created_by'),
      value: 'created_by.name',
      sortable: true,
      sortable_key: 'created_by',
    },
    {
      title: t('seatingplan::table_merges.table.closed_by'),
      value: 'closed_by.name',
      sortable: true,
      sortable_key: 'closed_by',
    },
    {
      title: t('seatingplan::table_merges.table.members'),
      value: 'members',
      sortable: true,
      sortable_key: 'table_id',
    },
    {
      title: t('seatingplan::table_merges.table.type'),
      value: 'type',
      sortable: true,
    },
    { title: t('seatingplan::table_merges.table.closed_at'), value: 'closed_at', sortable: true },
    { title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
  ]

  const cellComponents: Record<string, any> = {
    members: {
      component: TableMembers,
    },
  }
</script>

<template>
  <SmartDataTable
    api-uri="/v1/table-merges"
    :cell-components="cellComponents"
    :has-search="false"
    :headers="headers"
    module="seatingplan"
    name="table_merge"
    resource="table_merges"
  />
</template>
