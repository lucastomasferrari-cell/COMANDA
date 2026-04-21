<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import Empty from '../Empty.vue'
  import File from '../File.vue'
  import FilePlaceholderLoader from '../FilePlaceholderLoader.vue'
  import Breadcrumb from './Breadcrumb.vue'
  import Header from './Header.vue'

  const props = defineProps<{
    state: Record<string, any>
    search: Record<string, any>
    refresh: () => void
    breadcrumb: any[]
    isPicker?: boolean
    selectedFiles: (number | Record<string, any>)[]
    isSelected: (file: any) => boolean
    onClickFile: (event: any, file: any) => void
    onDblClickFile: (file: any) => void
    destroy: (ids: string | number) => void
    switchEditModal: (file: any) => void
    switchFileDetailsModal: (file: any) => void
    switchCreateFolderModal: () => void
    switchUploaderModal: () => void
    getMedia: (isRefreshing: boolean, skip: number, isSearching: boolean) => void
  }>()

  const { t } = useI18n()
  const { can } = useAuth()

  const fileContextMenu = reactive({
    visible: false,
    x: 0,
    y: 0,
    file: null as Record<string, any> | null,
  })

  const openFolder = (file: any) => {
    fileContextMenu.visible = false
    fileContextMenu.x = 0
    fileContextMenu.y = 0
    fileContextMenu.file = null
    props.onDblClickFile(file)
  }

  const openMenu = (event: any, file: any) => {
    fileContextMenu.visible = false
    fileContextMenu.x = event.clientX
    fileContextMenu.y = event.clientY
    fileContextMenu.file = file
    setTimeout(() => fileContextMenu.visible = true)
  }
  const download = (file: any) => {
    const link = document.createElement('a')
    link.href = file.download_url
    link.download = file.download_name || ''
    document.body.append(link)
    link.click()
    link.remove()
  }

  const disabled = computed(() => props.state.loading || props.state.refreshing || props.state.isLoadMore || props.search.loading)

  const onScroll = (event: Record<string, any>) => {
    if (event.target.scrollTop + event.target.clientHeight + 10 >= event.target.scrollHeight && props.state.stopLoadMore == false) {
      props.getMedia(false, props.state.files.length, false)
    }
  }
</script>

<template>
  <VCard id="file-manager">
    <VCardText>
      <Header
        :destroy="destroy"
        :disabled="disabled"
        :get-media="getMedia"
        :refresh="refresh"
        :refreshing="state.refreshing"
        :search="search"
        :selected-files="selectedFiles"
        :switch-create-folder-modal="switchCreateFolderModal"
        :switch-edit-modal="switchEditModal"
        :switch-file-details-modal="switchFileDetailsModal"
        :switch-uploader-modal="switchUploaderModal"
      />

      <Breadcrumb :folder-id="props.state.folderId" :items="breadcrumb" :tab="state.tab" @click="onDblClickFile" />

      <div
        class="card-text"
        :class="{'d-flex justify-center align-center':state.loading || state.files.length === 0}"
        :style="`height:${isPicker ? '75vh' : '65vh'}`"
        @scroll="onScroll"
      >
        <VProgressCircular
          v-if="state.loading && state.files.length === 0"
          color="primary"
          indeterminate
          size="50"
        />
        <Empty v-else-if="!state.loading && state.files.length === 0" :tab="state.tab" />
        <div v-else id="files-container">
          <File
            v-for="file in state.files"
            :key="file.id"
            :file="file"
            :selected="isSelected(file)"
            @click="onClickFile($event, file)"
            @contextmenu.prevent="openMenu($event,file)"
            @dblclick="onDblClickFile(file)"
          />
          <v-menu
            v-if="state.files.length > 0 && fileContextMenu.visible"
            v-model="fileContextMenu.visible"
            absolute
            internal-activator
            :target="[fileContextMenu.x,fileContextMenu.y]"
          >
            <v-list style="min-width: 190px;">
              <v-list-item v-if="!fileContextMenu.file?.is_file" @click="openFolder(fileContextMenu.file)">
                <template #prepend>
                  <v-icon icon="tabler-folder-open" />
                </template>
                <v-list-item-title>
                  {{ t('media::media.file_menu_items.open_folder') }}
                </v-list-item-title>
              </v-list-item>
              <v-list-item v-if="fileContextMenu.file?.is_file" @click="download(fileContextMenu.file)">
                <template #prepend>
                  <v-icon icon="tabler-cloud-download" />
                </template>
                <v-list-item-title>
                  {{ t('media::media.file_menu_items.download') }}
                </v-list-item-title>
              </v-list-item>
              <v-list-item v-if="can('admin.media.edit')" @click="switchEditModal(fileContextMenu.file)">
                <template #prepend>
                  <v-icon :icon="fileContextMenu.file?.type=='file'? 'tabler-photo-edit':'tabler-edit'" />
                </template>
                <v-list-item-title>
                  {{
                    t('media::media.file_menu_items.rename_resource', {
                      resource:
                        t(`media::media.${fileContextMenu.file?.is_file ? 'file' : 'folder'}`)
                    })
                  }}
                </v-list-item-title>
              </v-list-item>
              <v-list-item @click="switchFileDetailsModal(fileContextMenu.file)">
                <template #prepend>
                  <v-icon icon="tabler-info-square-rounded" />
                </template>
                <v-list-item-title>
                  {{
                    t('media::media.file_menu_items.resource_details', {
                      resource:
                        t(`media::media.${fileContextMenu.file?.is_file ? 'file' : 'folder'}`)
                    })
                  }}
                </v-list-item-title>
              </v-list-item>
              <v-divider v-if="can('admin.media.destroy')" />
              <v-list-item v-if="can('admin.media.destroy')" @click="destroy(fileContextMenu.file?.id)">
                <template #prepend>
                  <v-icon color="error" icon="tabler-trash" />
                </template>
                <v-list-item-title>
                  {{
                    t('media::media.file_menu_items.delete_resource', {
                      resource:
                        t(`media::media.${fileContextMenu.file?.is_file == true ? 'file' : 'folder'}`)
                    })
                  }}
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
          <template v-if="state.isLoadMore">
            <FilePlaceholderLoader
              v-for="item in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
              :key="item"
            />
          </template>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
#file-manager .card-text {
  position: relative;
  overflow-x: hidden;
  overflow-y: scroll;
}

#files-container {
  display: flex;
  flex-wrap: wrap;
}
</style>
