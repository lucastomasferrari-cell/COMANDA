<script lang="ts" setup>
  import type { IconValue } from 'vuetify/lib/composables/icons.js'
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()

  defineProps<{
    tab: string
  }>()

  const icon: Record<string, IconValue> = {
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
  <div class="text-center">
    <VIcon :icon="icon[tab]||'tabler-photo-video'" size="8rem" />
    <p class="message">
      {{ t(`media::media.no_data_available_${tab}`) }}
    </p>
  </div>
</template>

<style scoped>
.message {
  margin-top: 10px;
  font-size: 1rem;
}
</style>
