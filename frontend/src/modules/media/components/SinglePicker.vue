<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import MediaDialog from '@/modules/media/components/MediaDialog.vue'

  const props = defineProps<{
    modelValue: number | null | undefined
    media?: Record<string, any> | null | undefined
    label?: string
    mime?: string
    error?: string | null
    disabled?: boolean
  }>()

  const emit = defineEmits(['update:modelValue'])

  const { t } = useI18n()

  const file = ref<Record<string, any> | null | undefined>(props.media)
  const showMedia = ref(false)

  function browse () {
    if (props.disabled) {
      return
    }

    showMedia.value = true
  }

  function updateValue (newFile: Record<string, any> | null) {
    emit('update:modelValue', newFile == null ? null : newFile.id)
    file.value = newFile
  }

  function trash () {
    if (props.disabled) {
      return
    }

    updateValue(null)
  }

  function download () {
    if (props.disabled) {
      return
    }

    if (file.value) {
      const link = document.createElement('a')
      link.href = file.value.download_url
      link.download = file.value.download_name || ''
      document.body.append(link)
      link.click()
      link.remove()
    }
  }

  function callback (file: any) {
    if (props.disabled) {
      return
    }

    showMedia.value = false
    updateValue(file)
  }
</script>

<template>
  <div class="mb-3">
    <div v-if="label" class="mb-2 text-subtitle-1 font-weight-medium">
      {{ label }}
    </div>
    <div class="single-picker">
      <div class="picker-holder" :class="{ disabled: props.disabled }" role="button" @click="browse">
        <VIcon v-if="file == null" icon="tabler-photo-plus" />
        <template v-else>
          <img alt="" :src="file.preview_image_url">
        </template>
      </div>
      <template v-if="file != null">
        <div class="overlay gap-1">
          <VTooltip location="top">
            <template #activator="{ props }">
              <VBtn
                color="error"
                :disabled="props.disabled"
                icon
                rounded
                size="30"
                v-bind="props"
                @click="trash"
              >
                <VIcon icon="tabler-trash" />
              </VBtn>
            </template>
            <span>{{ t('admin::admin.buttons.delete') }}</span>
          </VTooltip>
          <VTooltip location="top">
            <template #activator="{ props }">
              <VBtn
                color="info"
                :disabled="props.disabled"
                icon
                rounded
                size="30"
                v-bind="props"
                @click="download"
              >
                <VIcon icon="tabler-cloud-download" />
              </VBtn>
            </template>
            <span>{{ t('admin::admin.buttons.download') }}</span>
          </VTooltip>
          <VTooltip location="top">
            <template #activator="{ props }">
              <VBtn
                color="primary"
                :disabled="props.disabled"
                icon
                rounded
                size="30"
                v-bind="props"
                @click="browse"
              >
                <VIcon icon="tabler-folder-open" />
              </VBtn>
            </template>
            <span>{{ t('admin::admin.buttons.browse') }}</span>
          </VTooltip>
        </div>
      </template>
    </div>
    <div v-if="error" class="mt-2 text-subtitle-2 text-error">
      {{ error }}
    </div>
  </div>
  <MediaDialog v-model="showMedia" :mime="mime" @callback="callback" />
</template>

<style lang="scss" scoped>
.single-picker {
  border-radius: 3px;
  border: 2px dashed #d9d9d9;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 5px;
  width: 150px;
  height: 150px;
  position: relative;
}

.picker-holder {
  overflow: hidden;
  height: 140px;
  width: 140px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

.picker-holder.disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.picker-holder img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.picker-holder i {
  font-size: 80px;
  color: #d9d9d9;
}

.picker-holder .file-name {
  width: 100%;
  overflow: hidden;
  font-size: 10px;
  font-weight: 500;
  white-space: nowrap;
  text-overflow: ellipsis;
  margin-top: 5px;
  text-align: center;
}

.single-picker .overlay {
  position: absolute;
  width: 100%;
  height: 100%;
  background: transparent;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  display: none;
  justify-content: flex-end;
  align-items: start;
  padding: 10px;
  transform: scale(0);
}

.single-picker:hover .overlay {
  display: flex;
  transform: scale(1);
}
</style>
