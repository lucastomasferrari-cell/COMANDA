<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  defineProps<{
    backups: Record<string, any>[]
    loading: boolean
    isAnyRestoreRunning: boolean
    isTableActionLoading: boolean
    downloadLoadingByFile: Record<string, boolean>
    restoreRowLoadingByFile: Record<string, boolean>
  }>()

  defineEmits<{
    backup: []
    download: [fileName: string]
    restore: [fileName: string]
  }>()

  const { t } = useI18n()
  const { can } = useAuth()
</script>

<template>
  <VCardText>
    <VAlert class="mb-4" color="info" variant="tonal">
      <div class="font-weight-bold mb-1">{{ t('tool::database.backup_note_title') }}</div>
      <div>{{ t('tool::database.backup_note_details') }}</div>
    </VAlert>

    <div class="d-flex justify-end">
      <VBtn
        v-if="can('admin.database_tools.backup')"
        color="primary"
        :disabled="isAnyRestoreRunning"
        :loading="loading"
        @click="$emit('backup')"
      >
        <VIcon icon="tabler-database-export" start />
        {{ t('tool::database.tabs.backup') }}
      </VBtn>
    </div>

    <div class="text-subtitle-1 font-weight-bold mb-3">
      {{ t('tool::database.latest_backups') }}
    </div>

    <VTable density="comfortable">
      <thead>
        <tr>
          <th>{{ t('tool::database.columns.name') }}</th>
          <th>{{ t('tool::database.columns.size') }}</th>
          <th>{{ t('tool::database.columns.created_at') }}</th>
          <th>{{ t('admin::admin.table.actions') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="backups.length === 0">
          <td class="text-medium-emphasis" colspan="4">
            {{ t('tool::database.no_backups') }}
          </td>
        </tr>
        <tr v-for="item in backups" :key="item.name">
          <td>{{ item.name }}</td>
          <td>{{ item.size_human }}</td>
          <td>{{ item.created_at }}</td>
          <td>
            <div class="d-flex align-center ga-2">
              <VBtn
                v-if="can('admin.database_tools.download')"
                :disabled="isTableActionLoading && !downloadLoadingByFile[item.name]"
                :loading="downloadLoadingByFile[item.name]"
                size="small"
                variant="tonal"
                @click="$emit('download', item.name)"
              >
                <VIcon icon="tabler-download" start />
                {{ t('admin::admin.buttons.download') }}
              </VBtn>
              <VBtn
                v-if="can('admin.database_tools.restore')"
                color="error"
                :disabled="isTableActionLoading && !restoreRowLoadingByFile[item.name]"
                :loading="restoreRowLoadingByFile[item.name]"
                size="small"
                variant="tonal"
                @click="$emit('restore', item.name)"
              >
                <VIcon icon="tabler-database-import" start />
                {{ t('tool::database.tabs.restore') }}
              </VBtn>
            </div>
          </td>
        </tr>
      </tbody>
    </VTable>
  </VCardText>
</template>
