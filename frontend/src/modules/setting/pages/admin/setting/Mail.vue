<script lang="ts" setup>

  import type { MailSettingsMeta, MailSettingsResponse } from '@/modules/setting/contracts/Settings.ts'
  import { useI18n } from 'vue-i18n'
  import PageLoader from '@/modules/core/components/PageLoader.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useSetting } from '@/modules/setting/composables/setting.ts'

  onMounted(async () => {
    const response: MailSettingsResponse | false = await getSettings('mail')
    if (response !== false) {
      meta.value = response.meta
      Object.assign(form.state, response.settings)
    }
  })

  const { getSettings, store, update } = useSetting()
  const { t } = useI18n()

  const meta = ref<MailSettingsMeta>({
    mailers: [],
    encryption_protocols: [],
  })

  const form = useForm({
    mail_mailer: '',
    mail_from_address: '',
    mail_from_name: '',
    mail_host: '',
    mail_port: '',
    mail_username: null,
    mail_password: null,
    mail_encryption: null,
  })

  function submit () {
    form.submit(() => update('mail', form.state))
  }
</script>

<template>
  <PageLoader :loading="store.loading" />
  <VForm v-if="!store.loading" @submit.prevent="submit">
    <VRow>
      <VCol cols="12" md="6">
        <VRow>
          <VCol cols="12">
            <VSelect
              v-model="form.state.mail_mailer"
              :error="!!form.errors.value?.mail_mailer"
              :error-messages="form.errors.value?.mail_mailer"
              item-title="name"
              item-value="id"
              :items="meta.mailers"
              :label="t('setting::attributes.settings.mail_mailer')"
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="form.state.mail_from_address"
              :error="!!form.errors.value?.mail_from_address"
              :error-messages="form.errors.value?.mail_from_address"
              :label="t('setting::attributes.settings.mail_from_address')"
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="form.state.mail_from_name"
              :error="!!form.errors.value?.mail_from_name"
              :error-messages="form.errors.value?.mail_from_name"
              :label="t('setting::attributes.settings.mail_from_name')"
            />
          </VCol>
          <template v-if="form.state.mail_mailer ==='smtp'">
            <VCol cols="12">
              <VTextField
                v-model="form.state.mail_host"
                :error="!!form.errors.value?.mail_host"
                :error-messages="form.errors.value?.mail_host"
                :label="t('setting::attributes.settings.mail_host')"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="form.state.mail_port"
                :error="!!form.errors.value?.mail_port"
                :error-messages="form.errors.value?.mail_port"
                :label="t('setting::attributes.settings.mail_port')"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="form.state.mail_username"
                :error="!!form.errors.value?.mail_username"
                :error-messages="form.errors.value?.mail_username"
                :label="t('setting::attributes.settings.mail_username')"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="form.state.mail_password"
                :error="!!form.errors.value?.mail_password"
                :error-messages="form.errors.value?.mail_password"
                :label="t('setting::attributes.settings.mail_password')"
              />
            </VCol>
            <VCol cols="12">
              <VSelect
                v-model="form.state.mail_encryption"
                :error="!!form.errors.value?.mail_encryption"
                :error-messages="form.errors.value?.mail_encryption"
                item-title="name"
                item-value="id"
                :items="meta.encryption_protocols"
                :label="t('setting::attributes.settings.mail_encryption')"
              />
            </VCol>
          </template>

        </VRow>
      </VCol>
    </VRow>
    <VRow>
      <VCol cols="12">
        <VBtn
          :disabled="form.loading.value"
          :loading="form.loading.value"
          type="submit"
        >
          <VIcon icon="tabler-mail-cog" start />
          {{ t('admin::admin.buttons.save') }}
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
