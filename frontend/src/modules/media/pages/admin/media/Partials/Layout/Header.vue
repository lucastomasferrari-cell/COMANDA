<script lang="ts" setup>
  import { debounce } from 'lodash'
  import { onBeforeUnmount, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const { t } = useI18n()
  const { can } = useAuth()

  const props = defineProps<{
    refresh: () => void
    destroy: (ids: string | number) => void
    switchEditModal: (file: any) => void
    switchFileDetailsModal: (file: any) => void
    switchCreateFolderModal: () => void
    getMedia: (isRefreshing: boolean, skip: number, isSearching: boolean) => void
    switchUploaderModal: () => void
    refreshing: boolean
    disabled: boolean
    selectedFiles: (Record<string, any> | number)[]
    search: Record<string, any>
  }>()

  const emitSearch = debounce(
    () => {
      props.getMedia(false, 0, true)
    },
    300)

  watch(() => props.search.text, () => emitSearch())

  onBeforeUnmount(() => {
    emitSearch.cancel()
  })
</script>

<template>
  <div class="d-flex align-center justify-space-between ">
    <div class="w-25">
      <VTextField
        v-model="search.text"
        :clearable="!search.loading"
        :disabled="disabled && !search.loading"
        :placeholder="t('media::media.search_placeholder')"
        prepend-inner-icon="tabler-folder-search"
      >
        <template #append-inner>
          <VProgressCircular
            v-if="search.loading"
            color="primary"
            indeterminate
            size="15"
            width="1.2"
          />
        </template>
      </VTextField>
    </div>
    <div class="d-flex align-center gap-7">
      <div class="d-flex align-center gap-2">
        <VTooltip :text="t('media::media.buttons.refresh')">
          <template #activator="{ props }">
            <VBtn
              color="success"
              :disabled="disabled"
              icon
              :loading="refreshing"
              rounded
              size="small"
              v-bind="props"
              @click="refresh"
            >
              <VIcon icon="tabler-refresh" />
            </VBtn>

          </template>
        </VTooltip>
      </div>
      <div class="d-flex align-center gap-2">
        <VTooltip :text="t('media::media.buttons.delete')">
          <template #activator="{ props }">
            <VBtn
              v-if="can('admin.media.destroy')"
              color="error"
              :disabled="disabled || selectedFiles.length === 0"
              icon
              rounded
              size="small"
              v-bind="props"
              @click="()=>destroy(selectedFiles.map((file:Record<string,any>|number)=>typeof file === 'number'?file: file.id).join(','))"
            >
              <VIcon icon="tabler-trash" />
            </VBtn>
          </template>
        </VTooltip>
        <VTooltip :text="t('media::media.buttons.details')">
          <template #activator="{ props }">
            <VBtn
              color="info"
              :disabled="disabled || selectedFiles.length!=1"
              icon
              rounded
              size="small"
              v-bind="props"
              @click="()=>switchFileDetailsModal(selectedFiles[0])"
            >
              <VIcon icon="tabler-info-circle" />
            </VBtn>
          </template>
        </VTooltip>
        <VTooltip :text="t('media::media.buttons.rename')">
          <template #activator="{ props }">
            <VBtn
              v-if="can('admin.media.edit')"
              color="warning"
              :disabled="disabled ||selectedFiles.length!=1"
              icon
              rounded
              size="small"
              v-bind="props"
              @click="()=>switchEditModal(selectedFiles[0])"
            >
              <VIcon icon="tabler-edit" />
            </VBtn>
          </template>
        </VTooltip>
      </div>
      <div class="d-flex align-center gap-2">
        <VTooltip :text="t('media::media.buttons.new_folder')">
          <template #activator="{ props }">
            <VBtn
              v-if="can('admin.media.create')"
              color="secondary"
              :disabled="disabled"
              icon
              rounded
              size="small"
              v-bind="props"
              @click="switchCreateFolderModal"
            >
              <VIcon icon="tabler-folder-plus" />
            </VBtn>
          </template>
        </VTooltip>
        <VTooltip :text="t('media::media.buttons.upload_file')">
          <template #activator="{ props }">
            <VBtn
              v-if="can('admin.media.create')"
              color="primary"
              :disabled="disabled"
              icon
              rounded
              size="small"
              v-bind="props"
              @click="switchUploaderModal"
            >
              <VIcon icon="tabler-upload" />
            </VBtn>
          </template>
        </VTooltip>
      </div>
    </div>
  </div>
</template>
