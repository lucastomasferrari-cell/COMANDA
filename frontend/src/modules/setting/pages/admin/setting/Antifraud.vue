<script lang="ts" setup>
  import { onMounted, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { http } from '@/modules/core/api/http.ts'

  const { t } = useI18n()
  const toast = useToast()

  const form = ref({
    'antifraud.discount_cashier_max_percent': 10,
    'antifraud.discount_manager_max_percent': 25,
    'antifraud.open_item_max_per_shift': 5,
    'antifraud.open_item_max_amount_each': 5000,
    'antifraud.open_item_max_total_per_shift': 20000,
    'antifraud.session_close_justification_threshold': 500,
    'antifraud.session_close_manager_required_percent': 10,
    'antifraud.daily_report_enabled': true,
    'antifraud.owner_alert_email': '',
    'antifraud.daily_report_hour': 6,
    'antifraud.allow_pending_without_manager': false,
  })

  const loading = ref(false)
  const saving = ref(false)
  const testingEmail = ref(false)

  async function fetchSettings () {
    loading.value = true
    try {
      const res = await http.get('/v1/settings/antifraud')
      const data = res.data.body ?? {}
      for (const key of Object.keys(form.value)) {
        if (data[key] !== undefined && data[key] !== null) {
          // @ts-expect-error dynamic
          form.value[key] = data[key]
        }
      }
    } catch {
      // silencioso: fresh install devuelve empty, usamos defaults del form.
    } finally {
      loading.value = false
    }
  }

  async function save () {
    saving.value = true
    try {
      await http.put('/v1/settings/antifraud/update', form.value)
      toast.success(t('setting::messages.settings_saved'))
    } catch (err: any) {
      toast.error(err?.response?.data?.message ?? t('core::errors.an_unexpected_error_occurred'))
    } finally {
      saving.value = false
    }
  }

  async function testEmail () {
    testingEmail.value = true
    try {
      // Dispara el command manualmente — si hay un endpoint no creado,
      // por ahora guardamos y el user lo testea vía CLI.
      await http.post('/v1/settings/antifraud/send-test-report')
      toast.success(t('setting::antifraud.test_sent'))
    } catch (err: any) {
      toast.error(err?.response?.data?.message ?? t('setting::antifraud.test_failed'))
    } finally {
      testingEmail.value = false
    }
  }

  onMounted(fetchSettings)
</script>

<template>
  <VCard v-if="!loading" class="pa-5">
    <h3 class="text-h6 mb-1">{{ t('setting::antifraud.title') }}</h3>
    <p class="text-caption text-medium-emphasis mb-5">
      {{ t('setting::antifraud.subtitle') }}
    </p>

    <!-- Umbrales descuento -->
    <h4 class="text-subtitle-1 font-weight-medium mb-2">
      {{ t('setting::antifraud.sections.discount_thresholds') }}
    </h4>
    <VRow dense>
      <VCol cols="12" md="6">
        <VTextField
          v-model.number="form['antifraud.discount_cashier_max_percent']"
          :label="t('setting::antifraud.fields.discount_cashier_max')"
          :suffix="'%'"
          type="number"
        />
      </VCol>
      <VCol cols="12" md="6">
        <VTextField
          v-model.number="form['antifraud.discount_manager_max_percent']"
          :label="t('setting::antifraud.fields.discount_manager_max')"
          :suffix="'%'"
          type="number"
        />
      </VCol>
    </VRow>
    <p class="text-caption text-medium-emphasis mt-1 mb-4">
      {{ t('setting::antifraud.fields.discount_explainer') }}
    </p>

    <VDivider class="my-4" />

    <!-- Open Item -->
    <h4 class="text-subtitle-1 font-weight-medium mb-2">
      {{ t('setting::antifraud.sections.open_item') }}
    </h4>
    <VRow dense>
      <VCol cols="12" md="4">
        <VTextField
          v-model.number="form['antifraud.open_item_max_per_shift']"
          :label="t('setting::antifraud.fields.open_item_max_per_shift')"
          type="number"
        />
      </VCol>
      <VCol cols="12" md="4">
        <VTextField
          v-model.number="form['antifraud.open_item_max_amount_each']"
          :label="t('setting::antifraud.fields.open_item_max_amount_each')"
          type="number"
        />
      </VCol>
      <VCol cols="12" md="4">
        <VTextField
          v-model.number="form['antifraud.open_item_max_total_per_shift']"
          :label="t('setting::antifraud.fields.open_item_max_total_per_shift')"
          type="number"
        />
      </VCol>
    </VRow>

    <VDivider class="my-4" />

    <!-- Arqueo -->
    <h4 class="text-subtitle-1 font-weight-medium mb-2">
      {{ t('setting::antifraud.sections.session_close') }}
    </h4>
    <VRow dense>
      <VCol cols="12" md="6">
        <VTextField
          v-model.number="form['antifraud.session_close_justification_threshold']"
          :label="t('setting::antifraud.fields.session_close_justification_threshold')"
          type="number"
        />
      </VCol>
      <VCol cols="12" md="6">
        <VTextField
          v-model.number="form['antifraud.session_close_manager_required_percent']"
          :label="t('setting::antifraud.fields.session_close_manager_required_percent')"
          :suffix="'%'"
          type="number"
        />
      </VCol>
    </VRow>

    <VDivider class="my-4" />

    <!-- Reporte diario -->
    <h4 class="text-subtitle-1 font-weight-medium mb-2">
      {{ t('setting::antifraud.sections.daily_report') }}
    </h4>
    <VSwitch
      v-model="form['antifraud.daily_report_enabled']"
      color="primary"
      density="compact"
      hide-details
      :label="t('setting::antifraud.fields.daily_report_enabled')"
    />
    <VRow class="mt-2" dense>
      <VCol cols="12" md="8">
        <VTextField
          v-model="form['antifraud.owner_alert_email']"
          :label="t('setting::antifraud.fields.owner_alert_email')"
          type="email"
        />
      </VCol>
      <VCol cols="12" md="4">
        <VTextField
          v-model.number="form['antifraud.daily_report_hour']"
          :hint="t('setting::antifraud.fields.daily_report_hour_hint')"
          :label="t('setting::antifraud.fields.daily_report_hour')"
          max="23"
          min="0"
          persistent-hint
          type="number"
        />
      </VCol>
      <VCol cols="12">
        <VBtn
          :disabled="!form['antifraud.owner_alert_email']"
          :loading="testingEmail"
          size="small"
          variant="tonal"
          @click="testEmail"
        >
          <VIcon icon="tabler-send" start />
          {{ t('setting::antifraud.fields.send_test_now') }}
        </VBtn>
      </VCol>
    </VRow>

    <VDivider class="my-4" />

    <!-- Autorizaciones pendientes -->
    <h4 class="text-subtitle-1 font-weight-medium mb-2">
      {{ t('setting::antifraud.sections.pending_policy') }}
    </h4>
    <VSwitch
      v-model="form['antifraud.allow_pending_without_manager']"
      color="primary"
      density="compact"
      hide-details
      :label="t('setting::antifraud.fields.allow_pending_without_manager')"
    />
    <p class="text-caption text-medium-emphasis mt-1 mb-2">
      {{ t('setting::antifraud.fields.allow_pending_explainer') }}
    </p>

    <VDivider class="my-4" />

    <div class="d-flex justify-end">
      <VBtn color="primary" :loading="saving" @click="save">
        <VIcon icon="tabler-device-floppy" start />
        {{ t('setting::messages.save') }}
      </VBtn>
    </div>
  </VCard>
  <div v-else class="d-flex justify-center py-10">
    <VProgressCircular color="primary" indeterminate size="40" />
  </div>
</template>
