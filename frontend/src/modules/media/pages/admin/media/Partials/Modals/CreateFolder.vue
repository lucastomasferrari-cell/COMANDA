<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useMedia } from '@/modules/media/composables/media.ts'
  import { useForm } from '@/modules/core/composables/form.ts'

  const props = defineProps<{
    modelValue: boolean
    folderId: number | null
    refresh: () => void
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const { t } = useI18n()
  const { createFolder } = useMedia()

  const form = useForm({
    folder_name: null,
    folder_id: props.folderId,
  })

  const close = () => emit('update:modelValue', false)

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => createFolder(form.state))
    ) {
      props.refresh()
      close()
    }
  }
</script>

<template>
  <v-dialog
    max-width="480"
    :model-value="modelValue"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <v-form @submit.prevent="submit">
      <v-card>
        <v-card-title>
          {{
            t('admin::resource.create', {resource: t('media::media.folder')})
          }}
        </v-card-title>
        <v-card-text>
          <v-text-field
            v-model="form.state.folder_name"
            autofocus
            :error="!!form.errors.value?.folder_name"
            :error-messages="form.errors.value?.folder_name"
            hide-details
            :label="t('media::attributes.folders.folder_name')"
          />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn color="default" :disabled="form.loading.value" variant="text" @click="close">
            {{ t('admin::admin.buttons.cancel') }}
          </v-btn>
          <v-btn
            :disabled="form.loading.value || !form.state.folder_name"
            :loading="form.loading.value"
            @click="submit"
          >
            {{ t('admin::admin.buttons.create') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>
