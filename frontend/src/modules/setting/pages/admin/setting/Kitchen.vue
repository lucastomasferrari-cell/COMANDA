<script lang="ts" setup>
  import type {
    KitchenSettings,
    KitchenSettingsMeta,
    KitchenSettingsResponse,
  } from '@/modules/setting/contracts/Settings.ts'
  import { useI18n } from 'vue-i18n'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useSetting } from '@/modules/setting/composables/setting.ts'

  onMounted(async () => {
    const response: KitchenSettingsResponse | false = await getSettings('kitchen')
    if (response !== false) {
      meta.value = response.meta
      Object.assign(form.state, response.settings)
    }
  })

  const { getSettings, store, update } = useSetting()
  const { t } = useI18n()

  const meta = ref<KitchenSettingsMeta>({
    modes: [],
  })

  const form = useForm<KitchenSettings>({
    auto_refresh_enabled: false,
    auto_refresh_mode: null,
    auto_refresh_interval: null,
    auto_refresh_pause_on_idle: false,
    auto_refresh_idle_timeout: null,
  })

  function submit () {
    form.submit(() => update('kitchen', form.state))
  }

  watch(() => form.state.auto_refresh_enabled, newValue => {
    if (!newValue) {
      form.state.auto_refresh_mode = null
      form.state.auto_refresh_interval = null
      form.state.auto_refresh_pause_on_idle = false
      form.state.auto_refresh_idle_timeout = null
    }
  })
</script>

<template>
  <PageLoader :loading="store.loading" />
  <VForm v-if="!store.loading" @submit.prevent="submit">
    <VRow>
      <VCol cols="12" md="8">
        <VRow>
          <VCol cols="12">
            <VCheckbox
              v-model="form.state.auto_refresh_enabled"
              :label="t('setting::attributes.settings.auto_refresh_enabled')"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-if="form.state.auto_refresh_enabled"
              v-model="form.state.auto_refresh_mode"
              :error="!!form.errors.value?.auto_refresh_mode"
              :error-messages="form.errors.value?.auto_refresh_mode"
              item-title="name"
              item-value="id"
              :items="meta.modes"
              :label="t('setting::attributes.settings.auto_refresh_mode')"
            />
          </VCol>
          <template
            v-if="form.state.auto_refresh_enabled && form.state.auto_refresh_mode==='smart_polling'"
          >
            <VCol cols="12">
              <VTextField
                v-model="form.state.auto_refresh_interval"
                v-integer-en
                :error="!!form.errors.value?.auto_refresh_interval"
                :error-messages="form.errors.value?.auto_refresh_interval"
                :label="t('setting::attributes.settings.auto_refresh_interval')"
              />
            </VCol>
            <VRow>
              <VCol cols="12" md="4">
                <VCheckbox
                  v-model="form.state.auto_refresh_pause_on_idle"
                  :label="t('setting::attributes.settings.auto_refresh_pause_on_idle')"
                />
              </VCol>
            </VRow>
            <VCol v-if="form.state.auto_refresh_pause_on_idle" cols="12">
              <VTextField
                v-model="form.state.auto_refresh_idle_timeout"
                v-integer-en
                :error="!!form.errors.value?.auto_refresh_idle_timeout"
                :error-messages="form.errors.value?.auto_refresh_idle_timeout"
                :label="t('setting::attributes.settings.auto_refresh_idle_timeout')"
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
          <VIcon icon="tabler-chef-hat" start />
          {{ t('admin::admin.buttons.save') }}
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
