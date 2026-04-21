<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    active: string
    disabled: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:active', value: string): void
  }>()

  const { t } = useI18n()

  const menuItems = [
    {
      group: t('media::media.sidebar.groups.folders'),
      items: [
        { icon: 'tabler-folder', title: t('media::media.sidebar.my_files'), value: 'my_files' },
        { icon: 'tabler-file-text', title: t('media::media.sidebar.documents'), value: 'documents' },
        { icon: 'tabler-photo', title: t('media::media.sidebar.images'), value: 'images' },
        { icon: 'tabler-video', title: t('media::media.sidebar.videos'), value: 'videos' },
        { icon: 'tabler-music', title: t('media::media.sidebar.audio'), value: 'audio' },
        { icon: 'tabler-archive', title: t('media::media.sidebar.archives'), value: 'archives' },
      ],
    },
    {
      group: t('media::media.sidebar.groups.types'),
      items: [
        { icon: 'tabler-file-type-pdf', title: t('media::media.sidebar.pdfs'), value: 'pdfs' },
        { icon: 'tabler-file-spreadsheet', title: t('media::media.sidebar.spreadsheets'), value: 'spreadsheets' },
        { icon: 'tabler-presentation', title: t('media::media.sidebar.presentations'), value: 'presentations' },
      ],
    },
  ]

  const changeActive = (value: string) => {
    emit('update:active', value)
  }
</script>

<template>
  <VCard>
    <VList dense nav>
      <template v-for="(group, index) in menuItems" :key="index">
        <VListSubheader>{{ group.group }}</VListSubheader>
        <VListItem
          v-for="item in group.items"
          :key="item.value"
          :active="item.value === props.active"
          :disabled="disabled"
          :prepend-icon="item.icon"
          :title="item.title"
          @click="changeActive(item.value)"
        />
      </template>
    </VList>
  </VCard>
</template>
<style lang="scss" scoped>
::v-deep(.v-list-item--nav .v-list-item-title) {
  line-height: 1.1rem;
}
</style>
