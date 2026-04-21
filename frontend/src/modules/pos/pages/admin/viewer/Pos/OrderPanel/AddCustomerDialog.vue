<script lang="ts" setup>
  import { ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { getFormMeta, store } from '@/modules/user/api/customer.api.ts'

  const dialogModel = defineModel<boolean>({ required: true })
  const emit = defineEmits<{
    created: [customer: Record<string, any>]
  }>()

  const { t } = useI18n()

  const countries = ref<Record<string, any>[]>([])

  const form = useForm({
    name: '',
    phone_country_iso_code: '',
    phone: '',
    customer_type: 'regular',
    is_active: true,
  })

  async function loadMeta () {
    try {
      const response = (await getFormMeta()).data.body
      countries.value = response.countries || []
    } catch {
      countries.value = []
    }
  }

  watch(dialogModel, async value => {
    if (!value) {
      return
    }
    form.state.name = ''
    form.state.phone = ''
    form.state.customer_type = 'regular'
    form.state.is_active = true
    await loadMeta()
  }, { immediate: true })

  async function submit () {
    const response = await form.submit(() => store(form.state), true)
    if (response && response !== true) {
      emit('created', response.data.body)
      dialogModel.value = false
    }
  }
</script>

<template>
  <VDialog v-model="dialogModel" max-width="40vw">
    <VCard>
      <VCardTitle class="d-flex align-center">
        <VIcon class="me-2" icon="tabler-user-plus" />
        {{ t('pos::pos_viewer.add_customer') }}
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="form.state.name"
              :error="!!form.errors.value?.name"
              :error-messages="form.errors.value?.name"
              :label="t('user::attributes.users.name')"
            />
          </VCol>
          <VCol cols="12" md="5">
            <VSelect
              v-model="form.state.phone_country_iso_code"
              :error="!!form.errors.value?.phone_country_iso_code"
              :error-messages="form.errors.value?.phone_country_iso_code"
              item-title="name"
              item-value="id"
              :items="countries"
              :label="t('user::attributes.users.phone_country_iso_code')"
            />
          </VCol>
          <VCol cols="12" md="7">
            <VTextField
              v-model="form.state.phone"
              :error="!!form.errors.value?.phone"
              :error-messages="form.errors.value?.phone"
              :label="t('user::attributes.users.phone')"
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions class="px-4 pb-4">
        <VSpacer />
        <VBtn variant="text" @click="dialogModel = false">
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
        <VBtn color="primary" :loading="form.loading.value" @click="submit">
          {{ t('admin::admin.buttons.create') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
