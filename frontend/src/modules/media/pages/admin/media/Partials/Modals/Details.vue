<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    modelValue: boolean
    file: Record<string, any>
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const { t } = useI18n()

  const close = () => emit('update:modelValue', false)

  const fileInfo = [
    'name',
    'mime',
    'extension',
    'human_size',
    'url',
    'uploaded_at',
  ]
</script>

<template>
  <v-dialog
    max-width="480"
    :model-value="modelValue"

    @update:model-value="emit('update:modelValue', $event)"
  >
    <v-card>
      <v-card-title>
        {{
          t('media::media.file_menu_items.resource_details', {resource: t(`media::media.${file.is_file ? 'file' : 'folder'}`)})
        }}
      </v-card-title>
      <v-card-text>
        <img alt="" :src="file.preview_image_url">
        <div class="info-container">
          <template v-for="(key) in fileInfo" :key="key">
            <div
              v-if="(!['mime','extension','human_size','url'].includes(key) && !file.is_file) || file.is_file"
              class="info"
            >
              <span class="title me-2">
                {{ t(`media::media.show_info.${(key == 'uploaded_at' && !file.is_file ? 'created_at' : key)}`) }}
              </span>:
              <span v-if="key=='url'" class="value">
                <a :href="file[key]" target="_blank">{{ file[key] }}</a>
              </span>
              <span v-else class="value">
                {{ file[key] }}
              </span>
            </div>
          </template>
        </div>
      </v-card-text>
      <v-card-actions class="justify-end">
        <v-btn color="default" variant="text" @click="close">
          {{ t('admin::admin.buttons.cancel') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style lang="scss" scoped>
img {
  width: 100px;
  height: 100px;
  object-fit: contain;
}

.info-container {
  margin-top: 20px;
}

.info-container .info {
  margin-top: 8px;
}

.info-container .info .title {
  font-weight: bold;
  width: 100px;
  display: inline-block;
}

.info-container .info .value {
  max-width: 300px;
  display: inline-block;
}
</style>
