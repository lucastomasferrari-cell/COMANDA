<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    form: Record<string, any>
    meta: Record<string, any>
  }>()

  const { t } = useI18n()
</script>

<template>

  <VCol cols="12" md="6">
    <VSelect
      v-model="form.state.meta.product_sku"
      :error="!!form.errors.value?.['meta.product_sku']"
      :error-messages="form.errors.value?.['meta.product_sku']"
      item-title="name"
      item-value="id"
      :items="meta.products"
      :label="t('loyalty::attributes.loyalty_rewards.meta.product_sku')"
    />
  </VCol>
  <VCol cols="12" md="6">
    <VTextField
      v-model="form.state.meta.quantity"
      v-integer-en
      :error="!!form.errors.value?.['meta.quantity']"
      :error-messages="form.errors.value?.['meta.quantity']"
      :label="t('loyalty::attributes.loyalty_rewards.meta.quantity')"
    />
  </VCol>
</template>
