<script lang="ts" setup>

  import type { LogoSettings, LogoSettingsResponse } from '@/modules/setting/contracts/Settings.ts'
  import { useI18n } from 'vue-i18n'
  import PageLoader from '@/modules/core/components/PageLoader.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import SinglePicker from '@/modules/media/components/SinglePicker.vue'
  import { useSetting } from '@/modules/setting/composables/setting.ts'

  const { getSettings, store, update } = useSetting()
  const { t } = useI18n()

  const form = useForm<{
    logo: number | null
    favicon: number | null
  }>({
    logo: null,
    favicon: null,
  })

  const logos = ref<LogoSettings>({
    logo: null,
    favicon: null,
  })

  onBeforeMount(async () => {
    const response: LogoSettingsResponse | false = await getSettings('logo')
    if (response !== false) {
      form.state.logo = response.settings.logo?.id
      form.state.favicon = response.settings.favicon?.id
      logos.value = response.settings
    }
  })

  function submit () {
    form.submit(() => update('logo', form.state))
  }
</script>

<template>
  <PageLoader :loading="store.loading" />
  <VForm v-if="!store.loading" @submit.prevent="submit">
    <VRow>
      <VCol cols="12" md="6">
        <SinglePicker
          :key="logos?.logo?.id"
          v-model="form.state.logo"
          :error="form.errors.value?.logo"
          :label="t('setting::attributes.settings.logo')"
          :media="logos?.logo"
          mime="image"
        />
      </VCol>
      <VCol cols="12" md="6">
        <SinglePicker
          :key="logos?.favicon?.id"
          v-model="form.state.favicon"
          :error="form.errors.value?.favicon"
          :label="t('setting::attributes.settings.favicon')"
          :media="logos?.favicon"
          mime="image"
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
          <VIcon icon="tabler-settings-cog" start />
          {{ t('admin::admin.buttons.save') }}
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
