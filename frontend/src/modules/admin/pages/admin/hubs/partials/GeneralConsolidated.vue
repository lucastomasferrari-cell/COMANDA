<script lang="ts" setup>
  import type {
    GeneralSettings,
    GeneralSettingsMeta,
    GeneralSettingsResponse,
  } from '@/modules/setting/contracts/Settings.ts'
  import { onMounted, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import PageLoader from '@/modules/core/components/PageLoader.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useSetting } from '@/modules/setting/composables/setting.ts'

  const { t } = useI18n()
  const { getSettings, store, update } = useSetting()

  const meta = ref<GeneralSettingsMeta>({
    countries: [],
    locales: [],
    timezones: [],
    date_formats: [],
    time_formats: [],
    days: [],
  })

  // Los fields no-editables (countries, timezone, date/time format, days)
  // siguen en el state y se mandan sin modificar al PUT. Se configuran
  // una sola vez en la instalación (AR defaults) y no tiene sentido que el
  // cajero los toque — el objetivo del plan es reducir opciones confusas.
  // Si en el futuro necesitamos un setup multi-país, se saca el hardcode.
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

  onMounted(async () => {
    const response: GeneralSettingsResponse | false = await getSettings('general')
    if (response !== false) {
      meta.value = response.meta
      Object.assign(form.state, response.settings)
    }
  })

  function submit () {
    form.submit(() => update('general', form.state))
  }
</script>

<template>
  <div class="d-flex flex-column ga-8">
    <!-- Sección 1: Datos del restaurante.
         La edición completa (nombre, CUIT, dirección, teléfono, email, logo)
         vive en el form de branch — hay una sola sucursal, así que linkeamos
         directo al edit del único record. CTA explícito en vez de embebido
         para no duplicar el form ni lidiar con router.push del submit. -->
    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('branch::branches.form.cards.branch_information') }}
      </h3>
      <VCard>
        <VCardText class="d-flex align-center justify-space-between flex-wrap ga-4">
          <div class="d-flex align-center">
            <VIcon class="me-3" icon="tabler-building-store" size="28" />
            <div>
              <div class="text-body-1 font-weight-medium">
                {{ t('branch::branches.form.cards.branch_information') }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                {{ t('branch::attributes.branches.name') }},
                {{ t('branch::attributes.branches.vat_tin') }},
                {{ t('branch::attributes.branches.address_line1') }},
                {{ t('branch::attributes.branches.phone') }},
                {{ t('branch::attributes.branches.email') }}
              </div>
            </div>
          </div>
          <VBtn
            color="primary"
            :to="{ name: 'admin.branches.edit', params: { id: 1 } }"
            variant="tonal"
          >
            <VIcon icon="tabler-pencil" start />
            {{ t('admin::admin.buttons.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </section>

    <!-- Sección 2: Idiomas.
         Único bloque realmente editable inline. supported_locales define qué
         idiomas se pueden asignar a nombres traducibles (productos, menús);
         default_locale es el del admin/cajero. -->
    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('setting::settings.sections.general') }}
      </h3>
      <PageLoader :loading="store.loading" />
      <VForm v-if="!store.loading" @submit.prevent="submit">
        <VRow>
          <VCol cols="12" md="6">
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
          <VCol cols="12" md="6">
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
            <VBtn
              :disabled="form.loading.value"
              :loading="form.loading.value"
              type="submit"
            >
              <VIcon icon="tabler-device-floppy" start />
              {{ t('admin::admin.buttons.save') }}
            </VBtn>
          </VCol>
        </VRow>
      </VForm>
    </section>
  </div>
</template>
