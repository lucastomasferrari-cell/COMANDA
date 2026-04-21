<script lang="ts" setup>
  import type { PwaSettings, PwaSettingsResponse } from '@/modules/setting/contracts/Settings.ts'
  import { useI18n } from 'vue-i18n'
  import ColorPicker from '@/modules/core/components/Form/ColorPicker.vue'
  import PageLoader from '@/modules/core/components/PageLoader.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import SinglePicker from '@/modules/media/components/SinglePicker.vue'
  import { useSetting } from '@/modules/setting/composables/setting.ts'

  const { getSettings, store, update } = useSetting()
  const { t } = useI18n()

  const form = useForm<{
    pwa_enabled: boolean
    pwa_name: string
    pwa_short_name: string
    pwa_icon: number | null
    pwa_background_color: string
    pwa_theme_color: string
    pwa_description: string | null
  }>({
    pwa_enabled: false,
    pwa_name: '',
    pwa_short_name: '',
    pwa_icon: null,
    pwa_background_color: '#ffffff',
    pwa_theme_color: '#ffffff',
    pwa_description: '',
  })

  const pwa = ref<PwaSettings>({
    pwa_enabled: false,
    pwa_name: '',
    pwa_short_name: '',
    pwa_icon: null,
    pwa_background_color: '#ffffff',
    pwa_theme_color: '#ffffff',
    pwa_description: '',
  })

  onBeforeMount(async () => {
    const response: PwaSettingsResponse | false = await getSettings('pwa')
    if (response !== false) {
      form.state.pwa_enabled = response.settings.pwa_enabled
      form.state.pwa_name = response.settings.pwa_name
      form.state.pwa_short_name = response.settings.pwa_short_name
      form.state.pwa_icon = response.settings.pwa_icon?.id ?? null
      form.state.pwa_background_color = response.settings.pwa_background_color
      form.state.pwa_theme_color = response.settings.pwa_theme_color
      form.state.pwa_description = response.settings.pwa_description
      pwa.value = response.settings
    }
  })

  function submit () {
    form.submit(() => update('pwa', form.state))
  }
</script>

<template>
  <PageLoader :loading="store.loading" />
  <VForm v-if="!store.loading" @submit.prevent="submit">
    <VRow>
      <VCol cols="12">
        <VSwitch
          v-model="form.state.pwa_enabled"
          :error="!!form.errors.value?.pwa_enabled"
          :error-messages="form.errors.value?.pwa_enabled"
          :label="t('setting::attributes.settings.pwa_enabled')"
        />
      </VCol>

      <VCol cols="12" md="6">
        <VTextField
          v-model="form.state.pwa_name"
          :disabled="!form.state.pwa_enabled"
          :error="!!form.errors.value?.pwa_name"
          :error-messages="form.errors.value?.pwa_name"
          :label="t('setting::attributes.settings.pwa_name')"
        />
      </VCol>

      <VCol cols="12" md="6">
        <VTextField
          v-model="form.state.pwa_short_name"
          :disabled="!form.state.pwa_enabled"
          :error="!!form.errors.value?.pwa_short_name"
          :error-messages="form.errors.value?.pwa_short_name"
          :label="t('setting::attributes.settings.pwa_short_name')"
        />
      </VCol>

      <VCol cols="12">
        <SinglePicker
          :key="pwa?.pwa_icon?.id"
          v-model="form.state.pwa_icon"
          :disabled="!form.state.pwa_enabled"
          :error="form.errors.value?.pwa_icon"
          :label="t('setting::attributes.settings.pwa_icon')"
          :media="pwa?.pwa_icon"
          mime="image"
        />
      </VCol>

      <VCol cols="12" md="6">
        <ColorPicker
          v-model="form.state.pwa_background_color"
          :disabled="!form.state.pwa_enabled"
          :error="!!form.errors.value?.pwa_background_color"
          :error-messages="form.errors.value?.pwa_background_color"
          :label="t('setting::attributes.settings.pwa_background_color')"
        />
      </VCol>

      <VCol cols="12" md="6">
        <ColorPicker
          v-model="form.state.pwa_theme_color"
          :disabled="!form.state.pwa_enabled"
          :error="!!form.errors.value?.pwa_theme_color"
          :error-messages="form.errors.value?.pwa_theme_color"
          :label="t('setting::attributes.settings.pwa_theme_color')"
        />
      </VCol>

      <VCol cols="12">
        <VTextarea
          v-model="form.state.pwa_description"
          :disabled="!form.state.pwa_enabled"
          :error="!!form.errors.value?.pwa_description"
          :error-messages="form.errors.value?.pwa_description"
          :label="t('setting::attributes.settings.pwa_description')"
          rows="4"
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
          <VIcon icon="tabler-device-mobile-cog" start />
          {{ t('admin::admin.buttons.save') }}
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
