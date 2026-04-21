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
      v-model="form.state.meta.target_tier"
      :error="!!form.errors.value?.['meta.target_tier']"
      :error-messages="form.errors.value?.['meta.target_tier']"
      item-title="name"
      item-value="id"
      :items="meta.tiers"
      :label="t('loyalty::attributes.loyalty_rewards.meta.target_tier')"
    />
  </VCol>
</template>
