<script lang="ts" setup>

  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { type RouteLocationRaw, useRouter } from 'vue-router'
  import TableEvent from '@/modules/activity/pages/admin/activityLog/Partials/TableEvent.vue'
  import TableLogName from '@/modules/activity/pages/admin/activityLog/Partials/TableLogName.vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'

  const { t } = useI18n()
  const { can } = useAuth()
  const router = useRouter()

  const headers: TableHeader[] = [
    { title: t('admin::admin.table.user'), value: 'user' },
    { title: t('activitylog::activity_logs.table.ip'), value: 'ip' },
    { title: t('admin::admin.table.agent'), value: 'agent' },
    { title: t('activitylog::activity_logs.table.log_name'), value: 'log_name', sortable: true },
    { title: t('activitylog::activity_logs.table.event'), value: 'event', sortable: true },
    { title: t('activitylog::activity_logs.table.subject'), value: 'subject', sortable: true },
    { title: t('activitylog::activity_logs.table.logged_at'), value: 'created_at', sortable: true },
  ]

  function showDetails (event: MouseEvent, value: any) {
    if (can('admin.activity_logs.show')) {
      router.push({
        name: 'admin.activity_logs.show',
        params: { id: value.item.id },
      } as unknown as RouteLocationRaw)
    }
  }
</script>

<template>
  <SmartDataTable
    api-uri="/v1/activity-logs"
    :cell-components="{
      log_name:{
        component:TableLogName,
      },
      event:{
        component:TableEvent,
        props: {
          color:'secondary'
        }
      },
    }"
    :has-search="false"
    :headers="headers"
    module="activitylog"
    name="activity_log"
    :on-row-click="showDetails"
    resource="activity_logs"
  />
</template>
