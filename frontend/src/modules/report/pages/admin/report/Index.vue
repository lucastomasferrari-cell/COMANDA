<script lang="ts" setup>

  import { useI18n } from 'vue-i18n'
  import { useReport } from '@/modules/report/composables/report.ts'
  import { reports } from '@/modules/report/pages/admin/report/reports.ts'

  const { t } = useI18n()
  const { goToReport, hasGroupPermissions, hasReportPermission } = useReport()
</script>

<template>
  <template v-for="(definitions,key) in reports" :key="key">
    <VRow v-if="hasGroupPermissions(key)">
      <VCol class="text-h6 font-weight-bold" cols="12">
        {{ t(`report::reports.groups.${key}`) }}
      </VCol>
      <template
        v-for="report in definitions"
        :key="report.key"
      >
        <VCol
          v-if="hasReportPermission(report.key)"
          cols="12"
          md="4"
          sm="6"
        >
          <VCard
            class="d-flex align-center gap-2 pa-4"
            min-height="103px"
            @click="goToReport(report.key)"
          >
            <VIcon color="primary" :icon="report.icon" size="48" />
            <div>
              <p class="mb-1 text-body-1 font-weight-bold">
                {{ t(`report::reports.definitions.${report.key}.title`) }}
              </p>
              <span class="text-body-2">{{
                t(`report::reports.definitions.${report.key}.description`)
              }}</span>
            </div>
          </VCard>
        </VCol>
      </template>
    </VRow>
  </template>
</template>
