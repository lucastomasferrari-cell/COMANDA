<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    form: Record<string, any>
    meta: Record<string, any>
  }>()

  const { t } = useI18n()
</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-reload" size="20" />
          <span>{{ t('loyalty::loyalty_promotions.form.cards.usage_rules') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.usage_limit"
              v-integer-en
              clearable
              :error="!!form.errors.value?.usage_limit"
              :error-messages="form.errors.value?.usage_limit"
              :label="t('loyalty::attributes.loyalty_promotions.usage_limit')"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.per_customer_limit"
              v-integer-en
              clearable
              :error="!!form.errors.value?.per_customer_limit"
              :error-messages="form.errors.value?.per_customer_limit"
              :label="t('loyalty::attributes.loyalty_promotions.per_customer_limit')"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
