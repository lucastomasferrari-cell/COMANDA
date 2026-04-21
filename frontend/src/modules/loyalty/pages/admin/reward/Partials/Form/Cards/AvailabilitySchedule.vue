<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    form: Record<string, any>
  }>()

  const { t } = useI18n()
</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-calendar-time" size="20" />
          <span>{{ t('loyalty::loyalty_rewards.form.cards.availability_and_schedule') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <DatePicker
              v-model="form.state.starts_at"
              clearable
              :error="!!form.errors.value?.starts_at"
              :error-messages="form.errors.value?.starts_at"
              :label="t('loyalty::attributes.loyalty_rewards.starts_at')"
              :min="new Date().toLocaleDateString('en-CA')"
            />
          </VCol>
          <VCol cols="12">
            <DatePicker
              v-model="form.state.ends_at"
              clearable
              :error="!!form.errors.value?.ends_at"
              :error-messages="form.errors.value?.ends_at"
              :label="t('loyalty::attributes.loyalty_rewards.ends_at')"
              :min="new Date().toLocaleDateString('en-CA')"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
