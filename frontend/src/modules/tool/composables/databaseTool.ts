import type { AxiosError } from 'axios'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { useAuth } from '@/modules/auth/composables/auth.ts'
import { useConfirmDialog } from '@/modules/core/composables/confirmDialog.ts'
import {
  backup,
  downloadBackup,
  index,
  restore,
  restoreFromBackup,
} from '@/modules/tool/api/database.api.ts'

export function useDatabaseTool () {
  const { t } = useI18n()
  const { can } = useAuth()
  const toast = useToast()

  const tab = ref('backup')
  const loading = ref(false)
  const restoreLoading = ref(false)
  const file = ref<File | null>(null)
  const backups = ref<Record<string, any>[]>([])
  const downloadLoadingByFile = ref<Record<string, boolean>>({})
  const restoreRowLoadingByFile = ref<Record<string, boolean>>({})

  const isTableActionLoading = computed(() => {
    return [
      ...Object.values(downloadLoadingByFile.value),
      ...Object.values(restoreRowLoadingByFile.value),
    ].some(Boolean)
  })

  const isAnyRestoreRunning = computed(() => {
    return restoreLoading.value || Object.values(restoreRowLoadingByFile.value).some(Boolean)
  })

  const isAnyActionRunning = computed(() => {
    return loading.value || restoreLoading.value || isTableActionLoading.value
  })

  function showError (error: unknown) {
    const message = (error as AxiosError<{ message?: string }>).response?.data?.message
      || t('core::errors.an_unexpected_error_occurred')

    toast.error(message)
  }

  async function loadData () {
    loading.value = true
    try {
      const response = (await index()).data.body
      backups.value = response.backups || []
    } catch (error) {
      showError(error)
    } finally {
      loading.value = false
    }
  }

  async function runBackup () {
    if (!can('admin.database_tools.backup')) {
      return
    }

    const confirmed = await useConfirmDialog({
      title: t('tool::database.confirmations.backup_title'),
      message: t('tool::database.confirmations.backup_message'),
      confirmButtonText: t('tool::database.tabs.backup'),
      cancelButtonText: t('admin::admin.buttons.cancel'),
      confirmColor: 'primary',
    })

    if (!confirmed) {
      return
    }

    loading.value = true
    try {
      const response = await backup()
      backups.value = response.data.body.backups || []
      toast.success(response.data.message || t('tool::database.messages.backup_created'))
    } catch (error) {
      showError(error)
    } finally {
      loading.value = false
    }
  }

  async function runRestoreFromBackup (fileName: string) {
    if (!can('admin.database_tools.restore')) {
      return
    }

    const confirmed = await useConfirmDialog({
      title: t('tool::database.confirmations.restore_title'),
      message: t('tool::database.confirmations.restore_saved_message'),
      confirmButtonText: t('tool::database.tabs.restore'),
      cancelButtonText: t('admin::admin.buttons.cancel'),
      confirmColor: 'error',
    })

    if (!confirmed) {
      return
    }

    restoreRowLoadingByFile.value[fileName] = true
    try {
      const response = await restoreFromBackup(fileName)
      await loadData()
      toast.success(response.data.message || t('tool::database.messages.restore_completed'))
    } catch (error) {
      showError(error)
    } finally {
      restoreRowLoadingByFile.value[fileName] = false
    }
  }

  async function runDownloadBackup (fileName: string) {
    if (!can('admin.database_tools.download')) {
      return
    }

    downloadLoadingByFile.value[fileName] = true
    try {
      const response = await downloadBackup(fileName)
      const blobUrl = window.URL.createObjectURL(response.data)
      const anchor = document.createElement('a')
      anchor.href = blobUrl
      anchor.download = fileName
      document.body.append(anchor)
      anchor.click()
      anchor.remove()
      window.URL.revokeObjectURL(blobUrl)
      toast.success(t('tool::database.messages.download_started'))
    } catch (error) {
      showError(error)
    } finally {
      downloadLoadingByFile.value[fileName] = false
    }
  }

  async function runRestore () {
    if (!can('admin.database_tools.restore') || !file.value) {
      return
    }

    const confirmed = await useConfirmDialog({
      title: t('tool::database.confirmations.restore_title'),
      message: t('tool::database.confirmations.restore_upload_message'),
      confirmButtonText: t('tool::database.tabs.restore'),
      cancelButtonText: t('admin::admin.buttons.cancel'),
      confirmColor: 'error',
    })

    if (!confirmed) {
      return
    }

    restoreLoading.value = true
    try {
      const response = await restore(file.value)
      file.value = null
      await loadData()
      toast.success(response.data.message || t('tool::database.messages.restore_completed'))
    } catch (error) {
      showError(error)
    } finally {
      restoreLoading.value = false
    }
  }

  return {
    tab,
    file,
    backups,
    loading,
    restoreLoading,
    downloadLoadingByFile,
    restoreRowLoadingByFile,
    isAnyActionRunning,
    isAnyRestoreRunning,
    isTableActionLoading,
    loadData,
    runBackup,
    runDownloadBackup,
    runRestoreFromBackup,
    runRestore,
  }
}
