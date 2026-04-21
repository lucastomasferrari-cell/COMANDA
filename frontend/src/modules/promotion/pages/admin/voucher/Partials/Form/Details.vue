<script lang="ts" setup>
  import { toRefs, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  const props = defineProps<{
    form: any
    meta: Record<string, any>
  }>()

  const { t } = useI18n()
  const { currency } = useAppStore()
  const { form } = toRefs(props)

  watch(
    () => props.form.state.type,
    newValue => {
      if (newValue == 'fixed') {
        props.form.state.max_discount = null
      }
    },
  )
</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-list-details" size="20" />
          <span>{{ t('voucher::vouchers.form.cards.details') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" md="4">
            <VSelect
              v-model="form.state.type"
              :error="!!form.errors.value?.type"
              :error-messages="form.errors.value?.type"
              item-title="name"
              item-value="id"
              :items="meta.types"
              :label="t('voucher::attributes.vouchers.type')"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.state.value"
              :error="!!form.errors.value?.value"
              :error-messages="form.errors.value?.value"
              :label="t('voucher::attributes.vouchers.value')"
              :prefix="form.state.type=='fixed'? currency:''"
              :suffix="form.state.type=='percent'?'%':''"
            />
          </VCol>
          <VCol v-if="form.state.type !='fixed'" cols="12" md="4">
            <VTextField
              v-model="form.state.max_discount"
              :error="!!form.errors.value?.max_discount"
              :error-messages="form.errors.value?.max_discount"
              :label="t('voucher::attributes.vouchers.max_discount')"
              :prefix="currency"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.minimum_spend"
              clearable
              :error="!!form.errors.value?.minimum_spend"
              :error-messages="form.errors.value?.minimum_spend"
              :label="t('voucher::attributes.vouchers.minimum_spend')"
              :prefix="currency"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.maximum_spend"
              clearable
              :error="!!form.errors.value?.maximum_spend"
              :error-messages="form.errors.value?.maximum_spend"
              :label="t('voucher::attributes.vouchers.maximum_spend')"
              :prefix="currency"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
