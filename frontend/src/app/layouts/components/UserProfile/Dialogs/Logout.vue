<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    modelValue: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()
  const { t } = useI18n()
  const auth = useAuth()

  const dialogModel = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val),
  })

  const close = () => emit('update:modelValue', false)

  function logout () {
    close()
    auth.logout()
  }

</script>

<template>
  <VDialog v-model="dialogModel" max-width="500">
    <VCard>
      <VCardTitle class="text-h6">{{ t('admin::navbar.logout_dialog.title') }}</VCardTitle>
      <VCardText>{{ t('admin::navbar.logout_dialog.body') }}</VCardText>
      <VCardActions>
        <VBtn color="dark" @click="close">{{ t('admin::admin.buttons.cancel') }}</VBtn>
        <VBtn @click="logout">{{ t('admin::navbar.logout') }}</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
