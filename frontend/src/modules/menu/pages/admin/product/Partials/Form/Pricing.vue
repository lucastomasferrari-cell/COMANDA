<script lang="ts" setup>
  import { toRefs } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{ form: any, meta: Record<string, any> }>()
  const { t } = useI18n()
  const { form } = toRefs(props)
</script>

<template>
  <VCard class="mt-3">
    <VCardTitle class="d-flex justify-space-between align-center mb-2">
      <div class="d-flex align-center">
        <VIcon class="me-2" icon="tabler-cash" size="20" />
        <span>{{ t('product::products.form.cards.pricing') }}</span>
      </div>
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12">
          <VTextField
            v-model="form.state.price"
            :error="!!form.errors.value?.price"
            :error-messages="form.errors.value?.price"
            :label="t('product::attributes.products.price')"
            :prefix=" meta.currency"
          />
        </VCol>
        <VCol cols="12">
          <VTextField
            v-model="form.state.special_price"
            :error="!!form.errors.value?.special_price"
            :error-messages="form.errors.value?.special_price"
            :label="t('product::attributes.products.special_price')"
            :prefix="meta.currency"
          />
        </VCol>
        <VCol cols="12">
          <VSelect
            v-model="form.state.special_price_type"
            clearable
            :error="!!form.errors.value?.special_price_type"
            :error-messages="form.errors.value?.special_price_type"
            item-title="name"
            item-value="id"
            :items="meta.priceTypes"
            :label="t('product::attributes.products.special_price_type')"
          />
        </VCol>
        <VCol cols="12">
          <DatePicker
            v-model="form.state.special_price_start"
            clearable
            :error="!!form.errors.value?.special_price_start"
            :error-messages="form.errors.value?.special_price_start"
            :label="t('product::attributes.products.special_price_start')"
            :min="new Date().toLocaleDateString('en-CA')"
          />
        </VCol>
        <VCol cols="12">
          <DatePicker
            v-model="form.state.special_price_end"
            clearable
            :error="!!form.errors.value?.special_price_end"
            :error-messages="form.errors.value?.special_price_end"
            :label="t('product::attributes.products.special_price_end')"
            :min="new Date().toLocaleDateString('en-CA')"
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
