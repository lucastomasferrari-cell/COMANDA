<script lang="ts" setup>
  import { computed, onBeforeUnmount, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = withDefaults(defineProps<{
    modelValue?: File | null
    initialUrl?: string | null
    label: string
    hint?: string
    error?: boolean
    errorMessages?: string | string[]
    disabled?: boolean
  }>(), {
    modelValue: null,
    initialUrl: null,
    hint: '',
    error: false,
    errorMessages: '',
    disabled: false,
  })

  const emit = defineEmits<{
    (e: 'update:modelValue', value: File | null): void
  }>()
  const { t } = useI18n()

  const inputRef = ref<HTMLInputElement | null>(null)
  const previewUrl = ref<string | null>(null)
  const isDragOver = ref(false)

  function revokePreviewUrl () {
    if (!previewUrl.value) return
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }

  watch(() => props.modelValue, file => {
    revokePreviewUrl()
    if (file instanceof File) {
      previewUrl.value = URL.createObjectURL(file)
    }
  }, { immediate: true })

  onBeforeUnmount(() => {
    revokePreviewUrl()
  })

  const displayUrl = computed(() => previewUrl.value || props.initialUrl || null)
  const normalizedErrors = computed(() => {
    if (Array.isArray(props.errorMessages)) return props.errorMessages
    if (props.errorMessages) return [props.errorMessages]
    return []
  })
  const resolvedHint = computed(() => props.hint || t('admin::admin.hints.profile_photo'))

  function openPicker () {
    if (props.disabled) return
    inputRef.value?.click()
  }

  const isAllowedType = (file: File) => ['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)

  function applyFile (file: File | null) {
    if (!file) return
    if (!isAllowedType(file)) return
    emit('update:modelValue', file)
  }

  function onFileChange (event: Event) {
    const target = event.target as HTMLInputElement | null
    const file = target?.files?.[0] || null
    applyFile(file)
  }

  function onDragOver (event: DragEvent) {
    if (props.disabled) return
    event.preventDefault()
    isDragOver.value = true
  }

  function onDragLeave () {
    isDragOver.value = false
  }

  function onDrop (event: DragEvent) {
    if (props.disabled) return
    event.preventDefault()
    isDragOver.value = false
    const file = event.dataTransfer?.files?.[0] || null
    applyFile(file)
  }

</script>

<template>
  <div
    class="profile-photo-picker"
    :class="{
      'profile-photo-picker--error': error,
      'profile-photo-picker--has-image': !!displayUrl,
      'profile-photo-picker--drag-over': isDragOver
    }"
    @click="openPicker"
    @dragleave="onDragLeave"
    @dragover="onDragOver"
    @drop="onDrop"
  >
    <div class="profile-photo-picker__preview">
      <VAvatar class="profile-photo-picker__avatar" size="124">
        <VImg v-if="displayUrl" cover :src="displayUrl" />
        <div v-else class="profile-photo-picker__placeholder-icon">
          <VIcon icon="tabler-user-circle" size="62" />
        </div>
      </VAvatar>
    </div>

    <input
      ref="inputRef"
      accept=".jpg,.jpeg,.png,image/jpeg,image/jpg,image/png"
      class="d-none"
      type="file"
      @change="onFileChange"
    >

    <div class="profile-photo-picker__placeholder">
      <div class="profile-photo-picker__title">
        <VIcon icon="tabler-cloud-upload" size="18" />
        <span>{{ label }}</span>
      </div>
    </div>

    <p v-if="resolvedHint" class="profile-photo-picker__hint">
      {{ resolvedHint }}
    </p>

    <p
      v-for="(message, index) in normalizedErrors"
      :key="index"
      class="profile-photo-picker__error"
    >
      {{ message }}
    </p>
  </div>
</template>

<style lang="scss" scoped>
.profile-photo-picker {
  border: 1px dashed rgba(var(--v-theme-primary), 0.35);
  border-radius: 16px;
  padding: 18px 16px 14px;
  background: linear-gradient(180deg, rgba(var(--v-theme-primary), 0.05), rgba(var(--v-theme-surface), 0.9));
  cursor: pointer;
  transition: border-color .2s ease, background-color .2s ease;
}

.profile-photo-picker:hover {
  border-color: rgba(var(--v-theme-primary), 0.62);
  background: linear-gradient(180deg, rgba(var(--v-theme-primary), 0.08), rgba(var(--v-theme-surface), 0.98));
}

.profile-photo-picker--drag-over {
  border-color: rgb(var(--v-theme-primary));
  background: linear-gradient(180deg, rgba(var(--v-theme-primary), 0.12), rgba(var(--v-theme-surface), 0.98));
}

.profile-photo-picker--error {
  border-color: rgb(var(--v-theme-error));
}

.profile-photo-picker--has-image {
  border-style: dashed;
}

.profile-photo-picker__preview {
  display: flex;
  justify-content: center;
  margin-bottom: 12px;
}

.profile-photo-picker__avatar {
  border: 3px solid rgba(var(--v-theme-primary), 0.18);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

.profile-photo-picker__placeholder-icon {
  height: 100%;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(var(--v-theme-primary), 0.75);
}

.profile-photo-picker__placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.profile-photo-picker__title {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: rgba(var(--v-theme-on-surface), 0.88);
  font-weight: 600;
  font-size: 15px;
}

.profile-photo-picker__subtitle {
  margin: 0;
  font-size: 12px;
  color: rgba(var(--v-theme-on-surface), 0.6);
  text-align: center;
}

.profile-photo-picker__hint {
  margin: 10px 0 0;
  color: rgba(var(--v-theme-on-surface), 0.58);
  font-size: 12px;
  text-align: center;
}

.profile-photo-picker__error {
  margin: 6px 0 0;
  color: rgb(var(--v-theme-error));
  font-size: 12px;
  text-align: center;
}
</style>
