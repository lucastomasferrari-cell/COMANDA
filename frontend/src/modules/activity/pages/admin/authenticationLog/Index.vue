<script lang="ts" setup>

  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()

  const headers: TableHeader[] = [
    { title: t('admin::admin.table.user'), value: 'user' },
    { title: t('admin::admin.table.agent'), value: 'agent' },
    { title: t('activitylog::authentication_logs.table.ip_address'), value: 'ip_address', sortable: true },
    { title: t('activitylog::authentication_logs.table.login_at'), value: 'login_at', sortable: true },
    { title: t('activitylog::authentication_logs.table.logout_at'), value: 'logout_at', sortable: true },
  ]

</script>

<template>
  <SmartDataTable
    api-uri="/v1/authentication-logs"
    :headers="headers"
    module="activitylog"
    name="authentication_log"
    resource="authentication_logs"
  />
</template>
