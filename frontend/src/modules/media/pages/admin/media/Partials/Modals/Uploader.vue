<script lang="ts" setup>
  import Dropzone from 'dropzone'
  import { useI18n } from 'vue-i18n'
  import { useAuthStore } from '@/modules/auth/stores/authStore.ts'

  const props = defineProps<{
    modelValue: boolean
    folderId: number | null
    maxFileSize: number | null | undefined
    acceptedFiles: string | null | undefined
    refresh: () => void
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const { t } = useI18n()
  const auth = useAuthStore()

  const dropRef = ref(null)

  const close = () => emit('update:modelValue', false)
  let dz: Dropzone

  onMounted(() => {
    if (dropRef.value !== null) {
      dz = new Dropzone(dropRef.value, {
        url: `${import.meta.env.VITE_API_URL.replace(/\/$/, '')}/v1/media?folder_id=${props.folderId || ''}`,
        autoProcessQueue: true,
        maxFiles: 10,
        maxFilesize: props.maxFileSize || undefined,
        acceptedFiles: props.acceptedFiles || undefined,
        headers: {
          Authorization: `Bearer ${auth.getToken}`,
          Accept: 'application/json',
        },
      })

      dz.on('error', (file, message: any) => {
        if (file.previewElement) {
          file.previewElement.classList.add('dz-error')

          // Normalize message
          if (typeof message !== 'string') {
            message = message?.message || 'Upload failed'
          }

          // Display error messages
          for (const node of file.previewElement
          .querySelectorAll('[data-dz-errormessage]')) {
            node.textContent = message
          }
        }
      })

      dz.on('success', () => {
        if (dz.getUploadingFiles().length === 0 && dz.getQueuedFiles().length === 0) {
          props.refresh?.()
        }
      })
    }
  })
</script>

<template>
  <v-dialog
    height="400"
    :model-value="modelValue"
    width="800"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        {{ t('media::media.upload_files') }}
        <v-btn color="default" icon @click="close">
          <v-icon icon="tabler-x" />
        </v-btn>
      </v-card-title>
      <v-card-text class="pt-0">
        <div ref="dropRef" class="uploader dropzone">
          <div class="dz-message">
            <v-icon class="icon" icon="tabler-cloud-upload" size="70" />
            <p class="mt-5">{{ t('media::media.uploader_description') }}</p>
          </div>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style>
@import "dropzone/dist/dropzone.css";

.uploader {
  width: 100%;
  height: 100%;
  border: 2px dashed #d2d6de;
  border-radius: 10px;
}

.dz-message {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  margin: 0 !important;
}

.uploader p {
  font-size: 1.1rem;
}
</style>
