<script lang="ts" setup>

  import type {
    CurrencySettings,
    CurrencySettingsMeta,
    CurrencySettingsResponse,
  } from '@/modules/setting/contracts/Settings.ts'
  import { useI18n } from 'vue-i18n'
  import PageLoader from '@/modules/core/components/PageLoader.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useSetting } from '@/modules/setting/composables/setting.ts'

  onMounted(async () => {
    const response: CurrencySettingsResponse | false = await getSettings('currency')
    if (response !== false) {
      meta.value = response.meta
      Object.assign(form.state, response.settings)
    }
  })

  const { getSettings, store, update } = useSetting()
  const { t } = useI18n()

  const meta = ref<CurrencySettingsMeta>({
    currencies: [],
    frequencies: [],
    exchange_services: [],
  })

  const form = useForm<CurrencySettings>({
    supported_currencies: [] as string[],
    default_currency: '',
    currency_rate_exchange_service: null,
    forge_api_key: null,
    fixer_access_key: null,
    currency_data_feed_api_key: null,
    auto_refresh_currency_rates: false,
    auto_refresh_currency_rate_frequency: null,
  })

  function submit () {
    form.submit(() => update('currency', form.state))
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
              v-model="form.state.supported_currencies"
              chips
              :error="!!form.errors.value?.supported_currencies"
              :error-messages="form.errors.value?.supported_currencies"
              item-title="name"
              item-value="id"
              :items="meta.currencies"
              :label="t('setting::attributes.settings.supported_currencies')"
              multiple
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.default_currency"
              :error="!!form.errors.value?.default_currency"
              :error-messages="form.errors.value?.default_currency"
              item-title="name"
              item-value="id"
              :items="meta.currencies"
              :label="t('setting::attributes.settings.default_currency')"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="form.state.currency_rate_exchange_service"
              clearable
              :error="!!form.errors.value?.currency_rate_exchange_service"
              :error-messages="form.errors.value?.currency_rate_exchange_service"
              item-title="name"
              item-value="id"
              :items="meta.exchange_services"
              :label="t('setting::attributes.settings.currency_rate_exchange_service')"
            />
          </VCol>
          <VCol v-if="form.state.currency_rate_exchange_service" cols="12">
            <VTextField
              v-if="form.state.currency_rate_exchange_service ==='forge'"
              v-model="form.state.forge_api_key"
              :error="!!form.errors.value?.forge_api_key"
              :error-messages="form.errors.value?.forge_api_key"
              :label="t('setting::attributes.settings.forge_api_key')"
              type="password"
            />
            <VTextField
              v-else-if="form.state.currency_rate_exchange_service ==='fixer'"
              v-model="form.state.fixer_access_key"
              :error="!!form.errors.value?.fixer_access_key"
              :error-messages="form.errors.value?.fixer_access_key"
              :label="t('setting::attributes.settings.fixer_access_key')"
              type="password"
            />
            <VTextField
              v-else-if="form.state.currency_rate_exchange_service ==='currency_data_feed'"
              v-model="form.state.currency_data_feed_api_key"
              :error="!!form.errors.value?.currency_data_feed_api_key"
              :error-messages="form.errors.value?.currency_data_feed_api_key"
              :label="t('setting::attributes.settings.currency_data_feed_api_key')"
              type="password"
            />
          </VCol>
          <VCheckbox
            v-model="form.state.auto_refresh_currency_rates"
            :label="t('setting::attributes.settings.auto_refresh_currency_rates')"
          />
          <VCol v-if="form.state.auto_refresh_currency_rates" cols="12">
            <VSelect
              v-model="form.state.auto_refresh_currency_rate_frequency"
              :error="!!form.errors.value?.auto_refresh_currency_rate_frequency"
              :error-messages="form.errors.value?.auto_refresh_currency_rate_frequency"
              item-title="name"
              item-value="id"
              :items="meta.frequencies"
              :label="t('setting::attributes.settings.auto_refresh_currency_rate_frequency')"
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
          <VIcon icon="tabler-currency-euro" start />
          {{ t('admin::admin.buttons.save') }}
        </VBtn>
      </VCol>
    </VRow>
  </VForm>

</template>
