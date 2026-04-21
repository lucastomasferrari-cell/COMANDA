<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  defineProps<{
    form: Record<string, any>
    meta: Record<string, any>
  }>()

  const { t } = useI18n()
  const { currency } = useAppStore()

</script>

<template>
  <VCol v-if="form.state.type ==='voucher_code'" cols="12" md="6">
    <VTextField
      v-model="form.state.meta.code_prefix"
      :error="!!form.errors.value?.['meta.code_prefix']"
      :error-messages="form.errors.value?.['meta.code_prefix']"
      :label="t('loyalty::attributes.loyalty_rewards.meta.code_prefix')"
    />
  </VCol>
  <VCol cols="12" md="6">
    <VTextField
      v-model="form.state.meta.expires_in_days"
      v-integer-en
      :error="!!form.errors.value?.['meta.expires_in_days']"
      :error-messages="form.errors.value?.['meta.expires_in_days']"
      :label="t('loyalty::attributes.loyalty_rewards.meta.expires_in_days')"
    />
  </VCol>
  <VCol cols="12" md="6">
    <VTextField
      v-model="form.state.meta.min_order_total"
      v-decimal-en
      :error="!!form.errors.value?.['meta.min_order_total']"
      :error-messages="form.errors.value?.['meta.min_order_total']"
      :label="t('loyalty::attributes.loyalty_rewards.meta.min_order_total')"
      :prefix="currency"
    />
  </VCol>
  <VCol cols="12" md="6">
    <VTextField
      v-model="form.state.meta.max_order_total"
      v-decimal-en
      :error="!!form.errors.value?.['meta.max_order_total']"
      :error-messages="form.errors.value?.['meta.max_order_total']"
      :label="t('loyalty::attributes.loyalty_rewards.meta.max_order_total')"
      :prefix="currency"
    />
  </VCol>
  <VCol v-if="form.state.value_type =='percent'" cols="12" md="6">
    <VTextField
      v-model="form.state.meta.max_discount"
      v-decimal-en
      :error="!!form.errors.value?.['meta.max_discount']"
      :error-messages="form.errors.value?.['meta.max_discount']"
      :label="t('loyalty::attributes.loyalty_rewards.meta.max_discount')"
      :prefix="currency"
    />
  </VCol>
</template>
