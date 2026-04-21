<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAccount } from '@/modules/user/composables/account.ts'

  const props = defineProps<{
    modelValue: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()
  const { t } = useI18n()
  const { updatePassword } = useAccount()
  const form = useForm({
    current_password: null,
    password: null,
    password_confirmation: null,
    logout_from_other_devices: false,
  })

  const dialogModel = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val),
  })
  const isDisabled = computed(() => form.loading.value || !form.state.current_password || !form.state.password || !form.state.password_confirmation)
  const close = () => emit('update:modelValue', false)

  async function submit () {
    if (!form.loading.value && await form.submit(() => updatePassword(form.state))) {
      close()
    }
  }

</script>

<template>
  <VDialog v-model="dialogModel" max-width="700">
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between">
        <div class="d-flex align-center gap-2">
          <VIcon color="primary" icon="tabler-user-hexagon" />
          <span>{{ t('admin::navbar.update_password') }}</span>
        </div>
        <VBtn icon variant="text" @click="close">
          <VIcon icon="tabler-x" />
        </VBtn>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="form.state.current_password"
              :error="!!form.errors.value?.current_password"
              :error-messages="form.errors.value?.current_password"
              :label="t('user::attributes.users.current_password')"
              type="password"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.password"
              :error="!!form.errors.value?.password"
              :error-messages="form.errors.value?.password"
              :label="t('user::attributes.users.password')"
              type="password"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.password_confirmation"
              :error="!!form.errors.value?.password_confirmation"
              :error-messages="form.errors.value?.password_confirmation"
              :label="t('user::attributes.users.password_confirmation')"
              type="password"
            />
          </VCol>
          <VCol cols="12">
            <VCheckbox
              v-model="form.state.logout_from_other_devices"
              :label="t('user::attributes.users.logout_from_other_devices')"
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn color="default" text @click="close">
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
        <VBtn
          color="primary"
          :disabled="isDisabled"
          :loading="form.loading.value"
          @click="submit"
        >
          {{ t('admin::admin.buttons.update') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
