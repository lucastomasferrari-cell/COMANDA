<script lang="ts" setup>
  import { computed, onBeforeUnmount, ref } from 'vue'

  const props = withDefaults(defineProps<{
    modelValue: File[]
    title?: string
    subtitle?: string
    actionLabel?: string
    error?: boolean
    errorMessages?: string | string[] | null
    multiple?: boolean
    accept?: string | string[] | null
  }>(), {
    modelValue: () => [],
    title: 'Upload attachments',
    subtitle: 'Drag & drop files here or click to browse.',
    actionLabel: 'Choose files',
    error: false,
    errorMessages: null,
    multiple: false,
    accept: null,
  })

  const emit = defineEmits<{
    (e: 'update:modelValue', value: File[]): void
  }>()

  const fileInputRef = ref<HTMLInputElement | null>(null)
  const isDragging = ref(false)

  const attachments = computed(() => props.modelValue || [])
  const previewUrls = new Map<File, string>()

  function getImagePreview (file: File) {
    if (!file.type.startsWith('image/')) return null
    if (previewUrls.has(file)) {
      return previewUrls.get(file) || null
    }
    const url = URL.createObjectURL(file)
    previewUrls.set(file, url)
    return url
  }

  function getFileIcon (file: File) {
    const name = file.name.toLowerCase()
    const ext = name.includes('.') ? name.split('.').pop() || '' : ''
    const type = file.type

    if (type === 'application/pdf' || ext === 'pdf') return 'tabler-file-type-pdf'
    if (['doc', 'docx'].includes(ext)) return 'tabler-file-text'
    if (['xls', 'xlsx', 'csv'].includes(ext)) return 'tabler-file-spreadsheet'
    if (['ppt', 'pptx'].includes(ext)) return 'tabler-file-chart'
    if (['zip', 'rar', '7z'].includes(ext)) return 'tabler-file-zip'
    if (['json', 'xml', 'yml', 'yaml'].includes(ext)) return 'tabler-file-code'
    return 'tabler-file'
  }

  function formatSize (bytes: number) {
    if (!Number.isFinite(bytes)) return '-'
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
  }

  function addFiles (files: FileList | File[]) {
    const list = Array.from(files)
    if (list.length === 0) return
    if (props.multiple) {
      emit('update:modelValue', [...attachments.value, ...list])
    } else {
      const first = list[0]
      if (first) {
        emit('update:modelValue', [first])
      }
    }
  }

  function onBrowseFiles () {
    fileInputRef.value?.click()
  }

  function onFileInputChange (event: Event) {
    const target = event.target as HTMLInputElement
    if (target?.files) {
      addFiles(target.files)
      target.value = ''
    }
  }

  function onDropFiles (event: DragEvent) {
    event.preventDefault()
    isDragging.value = false
    if (event.dataTransfer?.files) {
      addFiles(event.dataTransfer.files)
    }
  }

  function removeFile (index: number) {
    emit('update:modelValue', attachments.value.filter((_, i) => i !== index))
  }

  const acceptAttr = computed(() => {
    if (!props.accept) return undefined
    return Array.isArray(props.accept) ? props.accept.join(',') : props.accept
  })

  onBeforeUnmount(() => {
    for (const url of previewUrls.values()) URL.revokeObjectURL(url)
    previewUrls.clear()
  })
</script>

<template>
  <div>
    <div
      class="attachment-dropzone"
      :class="{'attachment-dropzone--active': isDragging}"
      @click="onBrowseFiles"
      @dragleave.prevent="isDragging = false"
      @dragover.prevent="isDragging = true"
      @drop="onDropFiles"
    >
      <div class="attachment-dropzone__icon">
        <VIcon icon="tabler-cloud-upload" size="24" />
      </div>
      <div class="attachment-dropzone__content">
        <div class="attachment-dropzone__title">{{ title }}</div>
        <div class="attachment-dropzone__subtitle">{{ subtitle }}</div>
      </div>
      <VChip
        v-if="attachments.length > 0"
        class="attachment-dropzone__count"
        color="secondary"
        size="small"
        variant="tonal"
      >
        {{ attachments.length }}
      </VChip>
      <VBtn class="attachment-dropzone__action" color="secondary" variant="tonal">
        {{ actionLabel }}
      </VBtn>
      <input
        ref="fileInputRef"
        :accept="acceptAttr"
        class="attachment-dropzone__input"
        :multiple="multiple"
        type="file"
        @change="onFileInputChange"
      >
    </div>
    <VAlert v-if="errorMessages" class="mt-3" type="error" variant="tonal">
      {{ errorMessages }}
    </VAlert>
    <div v-if="attachments.length > 0" class="attachment-list">
      <div
        v-for="(file, index) in attachments"
        :key="`${file.name}-${index}`"
        class="attachment-item"
      >
        <div class="attachment-item__info">
          <div class="attachment-item__thumb me-2">
            <VAvatar v-if="getImagePreview(file)" color="" rounded size="32">
              <VImg cover :src="getImagePreview(file) || ''" />
            </VAvatar>
            <VIcon v-else :icon="getFileIcon(file)" size="32" />
          </div>
          <div>
            <div class="attachment-item__name">{{ file.name }}</div>
            <div class="attachment-item__meta">{{ formatSize(file.size) }}</div>
          </div>
        </div>
        <VBtn color="error" icon variant="text" @click.stop="removeFile(index)">
          <VIcon icon="tabler-x" size="16" />
        </VBtn>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.attachment-dropzone {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border: 1px dashed rgba(var(--v-theme-secondary), 0.4);
  border-radius: 14px;
  background: linear-gradient(
      135deg,
      rgba(var(--v-theme-secondary), 0.08),
      rgba(var(--v-theme-secondary), 0.02)
  );
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.attachment-dropzone:hover {
  cursor: pointer;
}

.attachment-dropzone--active {
  border-color: rgba(var(--v-theme-secondary), 0.9);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
  transform: translateY(-1px);
}

.attachment-dropzone__icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  background: rgba(var(--v-theme-secondary), 0.15);
  color: rgb(var(--v-theme-secondary));
}

.attachment-dropzone__content {
  flex: 1;
}

.attachment-dropzone__title {
  font-weight: 600;
}

.attachment-dropzone__subtitle {
  font-size: 0.85rem;
  color: rgba(15, 23, 42, 0.6);
}

.attachment-dropzone__action {
  white-space: nowrap;
}

.attachment-dropzone__count {
  margin-left: auto;
}

.attachment-dropzone__input {
  display: none;
}

.attachment-list {
  margin-top: 12px;
  display: grid;
  gap: 10px;
}

.attachment-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.08);
}

.attachment-item:hover {
  cursor: pointer;
}

.attachment-item__info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.attachment-item__thumb {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  border: 1px dashed rgba(var(--v-theme-secondary), 0.45);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 5px;
  color: rgb(var(--v-theme-secondary));
  background: rgba(var(--v-theme-secondary), 0.06);
}

.attachment-item__name {
  font-weight: 600;
}

.attachment-item__meta {
  font-size: 0.8rem;
  color: rgba(15, 23, 42, 0.6);
}
</style>
