<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{ form: any }>()
  const { t } = useI18n()
</script>

<template>
  <VCard class="mt-3">
    <VCardTitle class="d-flex justify-space-between align-center mb-2">
      <div class="d-flex align-center">
        <VIcon class="me-2" icon="tabler-packages" size="20" />
        <span>{{ t('product::products.form.cards.additional') }}</span>
      </div>
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12">
          <DatePicker
            v-model="form.state.new_from"
            clearable
            :error="!!form.errors.value?.new_from"
            :error-messages="form.errors.value?.new_from"
            :label="t('product::attributes.products.new_from')"
            :min="new Date().toLocaleDateString('en-CA')"
          />
        </VCol>
        <VCol cols="12">
          <DatePicker
            v-model="form.state.new_to"
            clearable
            :error="!!form.errors.value?.new_to"
            :error-messages="form.errors.value?.new_to"
            :label="t('product::attributes.products.new_to')"
            :min="new Date().toLocaleDateString('en-CA')"
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>

</style>
