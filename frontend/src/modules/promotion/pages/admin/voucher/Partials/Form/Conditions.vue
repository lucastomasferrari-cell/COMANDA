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
          <VIcon class="me-2" icon="tabler-adjustments" size="20" />
          <span>{{ t('voucher::vouchers.form.cards.conditions') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.conditions.order_types"
              chips
              clearable
              :error="!!form.errors.value?.['conditions.order_types']"
              :error-messages="form.errors.value?.['conditions.order_types']"
              item-title="name"
              item-value="id"
              :items="meta.orderTypes"
              :label="t('voucher::attributes.vouchers.conditions.order_types')"
              multiple
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.conditions.available_days"
              chips
              clearable
              :error="!!form.errors.value?.['conditions.available_days']"
              :error-messages="form.errors.value?.['conditions.available_days']"
              item-title="name"
              item-value="id"
              :items="meta.days"
              :label="t('voucher::attributes.vouchers.conditions.available_days')"
              multiple
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.conditions.categories"
              chips
              clearable
              :error="!!form.errors.value?.['conditions.categories']"
              :error-messages="form.errors.value?.['conditions.categories']"
              item-title="name"
              item-value="slug"
              :items="meta.categories"
              :label="t('voucher::attributes.vouchers.conditions.categories')"
              multiple
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.conditions.products"
              chips
              clearable
              :error="!!form.errors.value?.['conditions.products']"
              :error-messages="form.errors.value?.['conditions.products']"
              item-title="name"
              item-value="id"
              :items="meta.products"
              :label="t('voucher::attributes.vouchers.conditions.products')"
              multiple
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
