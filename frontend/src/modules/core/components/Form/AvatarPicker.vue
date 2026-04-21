<script lang="ts" setup>
  import MediaDialog from '@/modules/media/components/MediaDialog.vue'

  type MediaValue = Record<string, any> | string | null | undefined

  const props = defineProps<{
    modelValue: number | null | undefined
    media?: MediaValue
    label?: string
    hint?: string
    error?: string | null
    mime?: string
    size?: number | string
  }>()

  const emit = defineEmits(['update:modelValue'])

  const file = ref<MediaValue>(props.media)
  const showMedia = ref(false)

  watch(
    () => props.media,
    value => {
      file.value = value
    },
  )

  const avatarSize = computed(() => props.size ?? 120)

  const previewUrl = computed(() => {
    if (typeof file.value === 'string') {
      return file.value
    }
    if (file.value && typeof file.value === 'object') {
      return (
        file.value.preview_image_url
        || file.value.url
        || file.value.download_url
        || ''
      )
    }
    return ''
  })

  const hasFile = computed(() => previewUrl.value.length > 0)

  function browse () {
    showMedia.value = true
  }

  function updateValue (newFile: Record<string, any> | null) {
    emit('update:modelValue', newFile == null ? null : newFile.id)
    file.value = newFile
  }

  function trash () {
    updateValue(null)
  }

  function callback (newFile: Record<string, any>) {
    showMedia.value = false
    updateValue(newFile)
  }
</script>

<template>
  <div class="avatar-picker">
    <div class="avatar-picker__circle" @click="browse">
      <span class="avatar-picker__ring" />
      <VAvatar class="avatar-picker__avatar" :size="avatarSize">
        <VImg v-if="hasFile" alt="" cover :src="previewUrl" />
        <div v-else class="avatar-picker__placeholder">
          <span class="avatar-picker__placeholder-head" />
          <span class="avatar-picker__placeholder-body" />
        </div>
      </VAvatar>
      <span class="avatar-picker__overlay">
        <VIcon icon="tabler-plus" size="22" />
      </span>
      <VBtn
        v-if="hasFile"
        class="avatar-picker__remove"
        color="error"
        icon
        size="32"
        variant="flat"
        @click.stop="trash"
      >
        <VIcon icon="tabler-trash" size="16" />
      </VBtn>
    </div>
    <div v-if="label" class="avatar-picker__title">
      {{ label }}
    </div>
    <div v-if="hint" class="avatar-picker__meta">
      {{ hint }}
    </div>
    <div v-if="error" class="avatar-picker__error">
      {{ error }}
    </div>
  </div>
  <MediaDialog v-model="showMedia" :mime="mime ?? 'image'" @callback="callback" />
</template>

<style lang="scss" scoped>
.avatar-picker {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 8px;
}

.avatar-picker__label {
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
}

.avatar-picker__circle {
  position: relative;
  border: none;
  padding: 8px;
  border-radius: 999px;
  background: transparent;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.avatar-picker__circle:hover {
  transform: translateY(-2px);
}

.avatar-picker__circle:focus-visible {
  outline: 3px solid rgba(var(--v-theme-primary), 0.35);
  outline-offset: 3px;
}

.avatar-picker__ring {
  position: absolute;
  inset: 2px;
  border-radius: 999px;
  border: 2px dashed rgba(var(--v-theme-primary), 0.3);
  pointer-events: none;
}

.avatar-picker__overlay {
  position: absolute;
  inset: 0;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(15, 23, 42, 0.2);
  color: #fff;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.avatar-picker__circle:hover .avatar-picker__overlay {
  opacity: 1;
}

.avatar-picker__avatar :deep(i) {
  font-size: 46px;
  color: rgba(var(--v-theme-on-surface), 0.35);
}

.avatar-picker__placeholder {
  position: relative;
  width: 70%;
  height: 70%;
  margin: 0 auto;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.03);
}

.avatar-picker__placeholder-head {
  position: absolute;
  top: 18%;
  left: 50%;
  width: 36%;
  height: 36%;
  transform: translateX(-50%);
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.08);
}

.avatar-picker__placeholder-body {
  position: absolute;
  bottom: 10%;
  left: 50%;
  width: 70%;
  height: 36%;
  transform: translateX(-50%);
  border-radius: 999px 999px 30px 30px;
  background: rgba(0, 0, 0, 0.08);
}

.avatar-picker__title {
  font-size: 16px;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
}

.avatar-picker__meta {
  font-size: 12px;
  color: rgba(var(--v-theme-on-surface), 0.5);
  max-width: 320px;
}

.avatar-picker__remove {
  position: absolute;
  inset-inline-end: 6px;
  inset-block-start: 6px;
  box-shadow: 0 8px 16px rgba(239, 68, 68, 0.25);
}

.avatar-picker__error {
  font-size: 12px;
  color: rgb(var(--v-theme-error));
}
</style>
