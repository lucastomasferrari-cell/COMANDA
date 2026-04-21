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
          <VIcon class="me-2" icon="tabler-barrier-block" size="20" />
          <span>{{ t('discount::discounts.form.cards.limits') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.usage_limit"
              clearable
              :error="!!form.errors.value?.usage_limit"
              :error-messages="form.errors.value?.usage_limit"
              :label="t('discount::attributes.discounts.usage_limit')"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.per_customer_limit"
              clearable
              :error="!!form.errors.value?.per_customer_limit"
              :error-messages="form.errors.value?.per_customer_limit"
              :label="t('discount::attributes.discounts.per_customer_limit')"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
