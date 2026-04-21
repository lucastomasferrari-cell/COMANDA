<script lang="ts" setup>
  import type { User } from '@/modules/auth/contracts/Auth.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAccount } from '@/modules/user/composables/account.ts'

  const props = defineProps<{
    modelValue: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()
  const { t } = useI18n()
  const { can, store } = useAuth()
  const { me, updateProfile } = useAccount()
  const toast = useToast()
  const form = useForm({
    name: null,
    email: null,
    username: null,
    gender: null,
    branch: null,
  })

  const dialogModel = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val),
  })
  const allowEdit = computed(() => can('admin.profiles.edit'))
  const isDisabled = computed(() => form.loading.value || !form.state.name || !form.state.username || !form.state.email)
  const loading = ref(true)

  const close = () => emit('update:modelValue', false)

  async function submit () {
    if (!form.loading.value) {
      const response = await form.submit(() => updateProfile(form.state), true)
      if (response && typeof response !== 'boolean') {
        store.setUser(response.data.body.user as User)
        close()
      }
    }
  }

  onBeforeMount(async () => {
    loading.value = true
    try {
      const response = await me()
      form.state.name = response.data.body.name
      form.state.email = response.data.body.email
      form.state.username = response.data.body.username
      form.state.gender = response.data.body.gender.name
      form.state.branch = response.data.body.branch?.name
    } catch {
      toast.error(t('core::errors.an_unexpected_error_occurred'))
      close()
    } finally {
      loading.value = false
    }
  })
</script>

<template>
  <VDialog v-model="dialogModel" max-width="700">
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between">
        <div class="d-flex align-center gap-2">
          <VIcon color="primary" icon="tabler-user-hexagon" />
          <span>{{ t('admin::navbar.my_account') }}</span>
        </div>
        <VBtn icon variant="text" @click="close">
          <VIcon icon="tabler-x" />
        </VBtn>
      </VCardTitle>
      <VCardText>
        <div v-if="loading" class="mt-10 mb-10 d-flex align-center justify-center">
          <VProgressCircular
            color="primary"
            indeterminate
            size="40"
            width="3"
          />
        </div>
        <VRow v-else>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.name"
              :error="!!form.errors.value?.name"
              :error-messages="form.errors.value?.name"
              :label="t('user::attributes.users.name')"
              :readonly="!allowEdit"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.email"
              :error="!!form.errors.value?.email"
              :error-messages="form.errors.value?.email"
              :label="t('user::attributes.users.email')"
              :readonly="!allowEdit"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.username"
              :error="!!form.errors.value?.username"
              :error-messages="form.errors.value?.username"
              :label="t('user::attributes.users.username')"
              :readonly="!allowEdit"
            />
          </VCol>
          <VCol v-if="form.state.branch" cols="12" md="6">
            <VTextField
              v-model="form.state.branch"
              :label="t('user::attributes.users.branch_id')"
              readonly
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.gender"
              :label="t('user::attributes.users.gender')"
              readonly
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions v-if="!loading && allowEdit">
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
