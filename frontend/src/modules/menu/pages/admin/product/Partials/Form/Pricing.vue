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
            :prefix="meta.currency"
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
