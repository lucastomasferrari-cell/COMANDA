<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    form: any
    meta: Record<string, any>
  }>()

  const { t } = useI18n()
  const { form } = toRefs(props)
</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-calendar-time" size="20" />
          <span>{{ t('discount::discounts.form.cards.availability') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <DatePicker
              v-model="form.state.start_date"
              clearable
              :error="!!form.errors.value?.start_date"
              :error-messages="form.errors.value?.start_date"
              :label="t('discount::attributes.discounts.start_date')"
              :min="new Date().toLocaleDateString('en-CA')"
            />
          </VCol>
          <VCol cols="12" md="6">
            <DatePicker
              v-model="form.state.end_date"
              clearable
              :error="!!form.errors.value?.end_date"
              :error-messages="form.errors.value?.end_date"
              :label="t('discount::attributes.discounts.end_date')"
              :min="new Date().toLocaleDateString('en-CA')"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
