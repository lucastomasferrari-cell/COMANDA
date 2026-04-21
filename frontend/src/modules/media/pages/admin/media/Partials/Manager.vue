<script lang="ts" setup>
  import type { AxiosError } from 'axios'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useMedia } from '@/modules/media/composables/media.ts'
  import { useConfirmDialog } from '@/modules/core/composables/confirmDialog.ts'
  import MediaContent from './Layout/Content.vue'
  import MediaSidebar from './Layout/Sidebar.vue'
  import CreateFolderModal from './Modals/CreateFolder.vue'
  import DetailsModal from './Modals/Details.vue'
  import EditModal from './Modals/Edit.vue'
  import UploaderModal from './Modals/Uploader.vue'

  const props = defineProps<{
    mime?: string
    isPicker?: boolean
    multiple?: boolean
  }>()
  const emit = defineEmits(['callback'])

  const media = useMedia()
  const toast = useToast()
  const { t } = useI18n()

  const state = reactive({
    tab: 'my_files',
    loading: false,
    refreshing: false,
    isLoadMore: false,
    stopLoadMore: false,
    folderId: null,
    focusFile: null as Record<string, any> | null,
    files: [],
  })
  const search = reactive({
    text: '',
    loading: false,
  })
  const modal = reactive({
    createFolder: false,
    uploader: false,
    edit: false,
    details: false,
    destroy: false,
  })
  const breadcrumb = ref<any[]>([])
  const selectedFiles = ref<(number | Record<string, any>)[]>([])
  const maxFileSize = ref(2)
  const acceptedFiles = ref(null)

  const getMedia = async (isRefreshing = false, skip = 0, isSearching = false) => {
    if (state.loading || state.refreshing || state.isLoadMore) {
      return
    }

    selectedFiles.value = []

    if (isSearching) {
      search.loading = true
      state.stopLoadMore = false
    } else if (isRefreshing) {
      state.refreshing = true
    } else if (skip > 0) {
      state.isLoadMore = true
    } else {
      state.files = []
      state.loading = true
    }

    try {
      const response = await media.get({
        filters: {
          search: search.text,
          mime: props.mime || '',
          folder: state.tab == 'my_files' ? (state.folderId || 'root') : null,
          tab: state.tab,
        },
        skip,
      })

      if (response.data.body.files.length === 0) {
        state.stopLoadMore = true
      }

      state.files = skip > 0
        ? [...state.files, ...response.data.body.files]
        : response.data.body.files
      maxFileSize.value = response.data.body.max_file_size
      acceptedFiles.value = response.data.body.accepted_files
    } catch {} finally {
      resetLoaders()
    }
  }

  const resetLoaders = () => {
    state.refreshing = false
    state.loading = false
    search.loading = false
    state.isLoadMore = false
  }

  const refresh = () => {
    state.stopLoadMore = false
    search.text = ''
    getMedia(true, 0)
  }

  const isSelected = (file: number | Record<string, any>) => {
    return selectedFiles.value.includes(file)
  }

  const onClickFile = (event: any, file: number | Record<string, any>) => {
    if (event.ctrlKey || event.metaKey) {
      if (isSelected(file)) {
        selectedFiles.value = selectedFiles.value.filter(f => f != file)
      } else {
        selectedFiles.value.push(file)
      }
    } else {
      selectedFiles.value = isSelected(file) ? [] : [file]
    }
  }

  const onDblClickFile = (file: any, fromBreadcrumb = false) => {
    if (
      !file.is_file
      && !state.loading
      && !state.refreshing
      && !state.isLoadMore
    ) {
      state.folderId = file.is_home ? null : file.id
      if (!file.is_home && fromBreadcrumb) {
        breadcrumb.value = breadcrumb.value.slice(
          0,
          breadcrumb.value.indexOf(file) + 1,
        )
      } else if (file.is_home) {
        breadcrumb.value = []
      } else {
        breadcrumb.value.push(file)
      }
      selectedFiles.value = []
      search.text = ''
      state.stopLoadMore = false
      state.files = []
      getMedia()
    } else if (props.isPicker && !props.multiple) {
      emit('callback', file)
    }
  }

  const switchCreateFolderModal = () => {
    modal.createFolder = !modal.createFolder
  }

  const switchUploaderModal = () => {
    modal.uploader = !modal.uploader
  }

  const switchEditModal = (file: any = null) => {
    if (modal.edit) {
      modal.edit = false
      setTimeout(() => (state.focusFile = null), 1)
    } else {
      state.focusFile = file
      setTimeout(() => (modal.edit = true), 1)
    }
  }

  const switchFileDetailsModal = (file: any = null) => {
    if (modal.details) {
      modal.details = false
      setTimeout(() => (state.focusFile = null), 1)
    } else {
      state.focusFile = file
      setTimeout(() => (modal.details = true), 1)
    }
  }

  const destroy = async (ids: string | number) => {
    const confirmed = await useConfirmDialog({
      message: t('admin::admin.delete.confirmation_message'),
      confirmButtonText: t('admin::admin.delete.confirm_button_text'),
    })
    if (!confirmed) {
      return
    }

    try {
      const response = await media.destroy(ids.toString())
      toast.success(response.data.message)
      refresh()
    } catch (error) {
      toast.error((error as AxiosError<{
        message?: string
      }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
    }
  }

  const onChangeTab = () => {
    state.folderId = null
    state.stopLoadMore = false
    search.text = ''
    breadcrumb.value = []
    getMedia()
  }

  onMounted(getMedia)
</script>

<template>
  <VRow no-gutters>
    <VCol class="pr-2" cols="12" md="3">
      <MediaSidebar
        v-model:active="state.tab"
        :disabled="state.loading || state.refreshing || search.loading || state.isLoadMore"
        @update:active="onChangeTab"
      />
    </VCol>
    <VCol cols="12" md="9">
      <MediaContent
        :breadcrumb="breadcrumb"
        :destroy="destroy"
        :get-media="getMedia"
        :is-picker="isPicker"
        :is-selected="isSelected"
        :on-click-file="onClickFile"
        :on-dbl-click-file="onDblClickFile"
        :refresh="refresh"
        :search="search"
        :selected-files="selectedFiles"
        :state="state"
        :switch-create-folder-modal="switchCreateFolderModal"
        :switch-edit-modal="switchEditModal"
        :switch-file-details-modal="switchFileDetailsModal"
        :switch-uploader-modal="switchUploaderModal"
      />
    </VCol>
  </VRow>
  <EditModal
    v-if="modal.edit && state.focusFile"
    v-model="modal.edit"
    :file="state.focusFile"
    :refresh="refresh"
  />
  <CreateFolderModal
    v-if="modal.createFolder"
    v-model="modal.createFolder"
    :folder-id="state.folderId"
    :refresh="refresh"
  />
  <DetailsModal
    v-if="modal.details && state.focusFile"
    v-model="modal.details"
    :file="state.focusFile"
  />
  <UploaderModal
    v-if="modal.uploader"
    v-model="modal.uploader"
    :accepted-files="acceptedFiles"
    :folder-id="state.folderId"
    :max-file-size="maxFileSize"
    :refresh="refresh"
  />
</template>
