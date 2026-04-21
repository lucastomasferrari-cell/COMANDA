<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  defineProps<{
    file: File | null
    restoreLoading: boolean
  }>()

  const emit = defineEmits<{
    'update:file': [value: File | null]
    'restore': []
  }>()

  const { t } = useI18n()
  const { can } = useAuth()

  function updateFile (value: File | File[] | null) {
    if (Array.isArray(value)) {
      emit('update:file', value[0] || null)
      return
    }

    emit('update:file', value)
  }
</script>

<template>
  <VCardText>
    <VAlert class="mb-4" color="error" variant="tonal">
      <div class="font-weight-bold mb-1">{{ t('tool::database.restore_warning_title') }}</div>
      <div>{{ t('tool::database.restore_warning_details') }}</div>
    </VAlert>

    <VFileInput
      v-if="can('admin.database_tools.restore')"
      accept=".sql,.txt"
      class="mb-4"
      :disabled="restoreLoading"
      :label="t('tool::database.backup_file')"
      :model-value="file"
      @update:model-value="updateFile"
    />

    <VBtn
      v-if="can('admin.database_tools.restore')"
      color="error"
      :disabled="!file || restoreLoading"
      :loading="restoreLoading"
      @click="$emit('restore')"
    >
      <VIcon icon="tabler-database-import" start />
      {{ t('tool::database.tabs.restore') }}
    </VBtn>
  </VCardText>
</template>
