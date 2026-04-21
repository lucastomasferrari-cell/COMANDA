<script lang="ts" setup>
  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'

  defineProps<{
    item: Record<string, any>
  }>()

  const { t } = useI18n()

  const headers: TableHeader[] = [
    { title: t('seatingplan::table_status_logs.table.changed_by'), value: 'changed_by.name' },
    { title: t('seatingplan::table_status_logs.table.status'), value: 'status' },
    { title: t('seatingplan::table_status_logs.table.note'), value: 'note' },
    { title: t('admin::admin.table.created_at'), value: 'created_at' },
  ]

</script>

<template>
  <SmartDataTable
    :api-uri="`/v1/tables/${item.id}/status-logs`"
    :has-search="false"
    :headers="headers"
    module="seatingplan"
    name="table_status_log"
    resource="table_status_logs"
    :title="t('seatingplan::tables.show.cards.status_history') "
  />
</template>
