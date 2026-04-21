<script lang="ts" setup>
  import type {
    GeneralSettings,
    GeneralSettingsMeta,
    GeneralSettingsResponse,
  } from '@/modules/setting/contracts/Settings.ts'
  import { useI18n } from 'vue-i18n'
  import PageLoader from '@/modules/core/components/PageLoader.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useSetting } from '@/modules/setting/composables/setting.ts'

  onMounted(async () => {
    const response: GeneralSettingsResponse | false = await getSettings('general')
    if (response !== false) {
      meta.value = response.meta
      Object.assign(form.state, response.settings)
    }
  })

  const { getSettings, store, update } = useSetting()
  const { t } = useI18n()

  const meta = ref<GeneralSettingsMeta>({
    countries: [],
    locales: [],
    timezones: [],
    date_formats: [],
    time_formats: [],
    days: [],
  })

  const form = useForm<GeneralSettings>({
    supported_countries: [] as string[],
    default_country: '',
    supported_locales: [] as string[],
    default_locale: '',
    default_timezone: '',
    default_date_format: '',
    default_time_format: '',
    start_of_week: '',
    end_of_week: '',
  })

  function submit () {
    form.submit(() => update('general', form.state))
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
              v-model="form.state.supported_countries"
              chips
              :error="!!form.errors.value?.supported_countries"
              :error-messages="form.errors.value?.supported_countries"
              item-title="name"
              item-value="id"
              :items="meta.countries"
              :label="t('setting::attributes.settings.supported_countries')"
              multiple
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.default_country"
              :error="!!form.errors.value?.default_country"
              :error-messages="form.errors.value?.default_country"
              item-title="name"
              item-value="id"
              :items="meta.countries"
              :label="t('setting::attributes.settings.default_country')"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.supported_locales"
              chips
              :error="!!form.errors.value?.supported_locales"
              :error-messages="form.errors.value?.supported_locales"
              item-title="name"
              item-value="id"
              :items="meta.locales"
              :label="t('setting::attributes.settings.supported_locales')"
              multiple
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.default_locale"
              :error="!!form.errors.value?.default_locale"
              :error-messages="form.errors.value?.default_locale"
              item-title="name"
              item-value="id"
              :items="meta.locales"
              :label="t('setting::attributes.settings.default_locale')"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.default_timezone"
              :error="!!form.errors.value?.default_timezone"
              :error-messages="form.errors.value?.default_timezone"
              item-title="name"
              item-value="id"
              :items="meta.timezones"
              :label="t('setting::attributes.settings.default_timezone')"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.default_date_format"
              :error="!!form.errors.value?.default_date_format"
              :error-messages="form.errors.value?.default_date_format"
              item-title="name"
              item-value="id"
              :items="meta.date_formats"
              :label="t('setting::attributes.settings.default_date_format')"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.default_time_format"
              :error="!!form.errors.value?.default_time_format"
              :error-messages="form.errors.value?.default_time_format"
              item-title="name"
              item-value="id"
              :items="meta.time_formats"
              :label="t('setting::attributes.settings.default_time_format')"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.start_of_week"
              :error="!!form.errors.value?.start_of_week"
              :error-messages="form.errors.value?.start_of_week"
              item-title="name"
              item-value="id"
              :items="meta.days"
              :label="t('setting::attributes.settings.start_of_week')"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.end_of_week"
              :error="!!form.errors.value?.end_of_week"
              :error-messages="form.errors.value?.end_of_week"
              item-title="name"
              item-value="id"
              :items="meta.days"
              :label="t('setting::attributes.settings.end_of_week')"
            />
          </VCol>
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
          <VIcon icon="tabler-settings-cog" start />
          {{ t('admin::admin.buttons.save') }}
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
