<script lang="ts" setup>

  import type {
    ApplicationSettings,
    ApplicationSettingsResponse,
    SupportedLanguagesState,
  } from '@/modules/setting/contracts/Settings.ts'
  import { useI18n } from 'vue-i18n'
  import FormLanguageSwitcher from '@/modules/core/components/Form/FormLanguageSwitcher.vue'
  import PageLoader from '@/modules/core/components/PageLoader.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'
  import { useSetting } from '@/modules/setting/composables/setting.ts'

  const { getSettings, store, update } = useSetting()
  const { defaultLanguage } = useAppStore()
  const { t } = useI18n()

  const form = useForm<ApplicationSettings>({
    app_name: {},
  })

  onMounted(async () => {
    const response: ApplicationSettingsResponse | false = await getSettings('application')
    if (response !== false) {
      Object.assign(form.state, response.settings)
    }
  })

  function submit () {
    form.submit(() => update('application', form.state))
  }

  const currentLanguage = ref<SupportedLanguagesState>(defaultLanguage)
</script>

<template>
  <PageLoader :loading="store.loading" />
  <template v-if="!store.loading">
    <VRow>
      <VCol class="d-flex justify-end" cols="12">
        <FormLanguageSwitcher v-model="currentLanguage" class="form-language-switcher-settings" />
      </VCol>
    </VRow>
    <VForm @submit.prevent="submit">
      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.state.app_name[currentLanguage.id]"
            :error="!!form.errors.value?.[`app_name.${currentLanguage.id}`]"
            :error-messages="form.errors.value?.[`app_name.${currentLanguage.id}`]"
            :label="t('setting::attributes.settings.app_name') + ` ( ${currentLanguage.name} )`"
          />
        </VCol>
      </VRow>
      <VRow>
        <VCol cols="12">
          <VBtn
            :disabled="form.loading.value"
            :loading="form.loading.value"
            type="submit"
          >
            <VIcon icon="tabler-settings-2" start />
            {{ t('admin::admin.buttons.save') }}
          </VBtn>
        </VCol>
      </VRow>
    </VForm>
  </template>
</template>
