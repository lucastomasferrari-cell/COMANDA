<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useMedia } from '@/modules/media/composables/media.ts'
  import { useForm } from '@/modules/core/composables/form.ts'

  const props = defineProps<{
    modelValue: boolean
    file: Record<string, any>
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const { t } = useI18n()
  const { update } = useMedia()

  const form = useForm({
    name: props.file.name,
  })

  const close = () => emit('update:modelValue', false)

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => update(props.file?.id, form.state))
    ) {
      // eslint-disable-next-line vue/no-mutating-props
      props.file.name = form.state.name
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
            t('media::media.file_menu_items.edit_resource', {resource: t(`media::media.${file.is_file ? 'file' :
              'folder'}`)})
          }}
        </v-card-title>
        <v-card-text>
          <v-text-field
            v-model="form.state.name"
            autofocus
            :error="!!form.errors.value?.name"
            :error-messages="form.errors.value?.name"
            hide-details
            :label="t('media::attributes.media.name')"
          />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn color="default" :disabled="form.loading.value" variant="text" @click="close">
            {{ t('admin::admin.buttons.cancel') }}
          </v-btn>
          <v-btn :disabled="form.loading.value || !form.state.name" :loading="form.loading.value" @click="submit">
            {{ t('admin::admin.buttons.update') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>
