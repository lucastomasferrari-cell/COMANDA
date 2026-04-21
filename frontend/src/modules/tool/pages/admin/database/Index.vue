<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useDatabaseTool } from '@/modules/tool/composables/databaseTool.ts'
  import BackupTab from '@/modules/tool/pages/admin/database/Partials/BackupTab.vue'
  import RestoreTab from '@/modules/tool/pages/admin/database/Partials/RestoreTab.vue'

  const { t } = useI18n()
  const {
    backups,
    downloadLoadingByFile,
    file,
    isAnyActionRunning,
    isAnyRestoreRunning,
    isTableActionLoading,
    loadData,
    loading,
    restoreLoading,
    restoreRowLoadingByFile,
    runBackup,
    runDownloadBackup,
    runRestore,
    runRestoreFromBackup,
    tab,
  } = useDatabaseTool()

  onBeforeMount(async () => {
    await loadData()
  })
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <VTabs v-model="tab" class="px-4">
          <VTab :disabled="isAnyActionRunning" value="backup">
            {{ t('tool::database.tabs.backup') }}
          </VTab>
          <VTab :disabled="isAnyActionRunning" value="restore">
            {{ t('tool::database.tabs.restore') }}
          </VTab>
        </VTabs>

        <VWindow v-model="tab">
          <VWindowItem value="backup">
            <BackupTab
              :backups="backups"
              :download-loading-by-file="downloadLoadingByFile"
              :is-any-restore-running="isAnyRestoreRunning"
              :is-table-action-loading="isTableActionLoading"
              :loading="loading"
              :restore-row-loading-by-file="restoreRowLoadingByFile"
              @backup="runBackup"
              @download="runDownloadBackup"
              @restore="runRestoreFromBackup"
            />
          </VWindowItem>

          <VWindowItem value="restore">
            <RestoreTab
              :file="file"
              :restore-loading="restoreLoading"
              @restore="runRestore"
              @update:file="file = $event"
            />
          </VWindowItem>
        </VWindow>
      </VCard>
    </VCol>
  </VRow>
</template>
