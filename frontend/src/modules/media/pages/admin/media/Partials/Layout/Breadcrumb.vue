<script lang="ts" setup>
  import type { IconValue } from 'vuetify/lib/composables/icons.js'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    folderId: number | null
    tab: string
    items: any[]
  }>()

  const emit = defineEmits(['click'])

  const { t } = useI18n()

  const onClick = (file: Record<string, any>) => {
    if ((file.id == props.folderId) || (file.is_home && props.items.length === 0)) {
      return null
    }
    emit('click', file, true)
  }

  const homeIcon: Record<string, IconValue> = {
    my_files: 'tabler-folder',
    documents: 'tabler-file-text',
    images: 'tabler-photo',
    videos: 'tabler-video',
    audio: 'tabler-music',
    archives: 'tabler-archive',
    pdfs: 'tabler-file-type-pdf',
    spreadsheets: 'tabler-file-spreadsheet',
    presentations: 'tabler-presentation',
  }
</script>

<template>
  <VBreadcrumbs class="px-2">

    <VBreadcrumbsItem :class="[{'cursor-pointer':items.length>0}]" @click="onClick({ is_file: false, is_home: true })">
      <v-icon class="me-1" :icon="homeIcon[tab]" size="18" />
      {{ t(`media::media.sidebar.${tab}`) }}
    </VBreadcrumbsItem>
    <VBreadcrumbsItem
      v-for="(item, index) in items"
      :key="item.id"
      :active="index === items.length - 1"
      class="d-flex align-center gap-1"
      :class="[{'cursor-pointer': index !== items.length - 1}]"
      @click="onClick(item)"
    >
      <VIcon icon="tabler-chevron-right" size="18" />
      <span :class="[{ 'fsont-weight-bold': index !== props.items.length - 1 }]">
        {{ item.name }}
      </span>

    </VBreadcrumbsItem>
  </VBreadcrumbs>
</template>

<style lang="scss" scoped>

</style>
