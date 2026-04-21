<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    modelValue: boolean
    title?: string
    message?: string
    confirmButtonText?: string
    cancelButtonText?: string
    confirmColor?: string
  }>()

  const { t } = useI18n()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'close', confirmed: boolean): void
  }>()

  const close = () => emit('update:modelValue', false)

  const confirm = () => {
    emit('close', true)
    close()
  }

  const cancel = () => {
    emit('close', false)
    close()
  }
</script>

<template>
  <VDialog
    max-width="400"
    :model-value="modelValue"
    persistent
    @update:model-value="val => emit('update:modelValue', val)"
  >
    <VCard>
      <VCardTitle class="text-h6 font-weight-bold">
        {{ title || t('admin::admin.default_confirmation.title') }}
      </VCardTitle>

      <VCardText>
        {{ message || t('admin::admin.default_confirmation.message') }}
      </VCardText>

      <VCardActions class="justify-end">
        <VBtn color="default" variant="text" @click="cancel">
          {{ cancelButtonText || t('admin::admin.default_confirmation.cancel_button_text') }}
        </VBtn>
        <VBtn :color="confirmColor || 'primary'" @click="confirm">
          {{ confirmButtonText || t('admin::admin.default_confirmation.confirm_button_text') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
