<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useForm } from '@/modules/core/composables/form.ts'
  import {
    useCurrencyRate,
  } from '@/modules/currency/composables/currencyRate.ts'

  const props = defineProps<{ modelValue: boolean, item: any }>()
  const emit = defineEmits(['update:modelValue', 'saved'])

  const { t } = useI18n()
  const { updateCurrencyRate } = useCurrencyRate()

  const form = useForm({
    rate: props.item?.rate || '',
  })

  function close () {
    emit('update:modelValue', false)
  }

  async function submit () {
    if (form.state.rate && !form.loading.value && await form.submit(() => updateCurrencyRate(props.item.id, form.state.rate))) {
      emit('saved')
      close()
    }
  }
</script>

<template>
  <VDialog
    max-width="500"
    :model-value="modelValue"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VForm @submit.prevent="submit">
      <VCard>
        <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-1 font-weight-bold text-h6">
          <VIcon icon="ic-outline-edit" size="20" />
          {{ t("admin::resource.edit", {resource: t("currency::currency_rates.currency_rate")}) }}
        </VCardTitle>
        <VCardText class="pt-0">
          <VTextField
            v-model="form.state.rate"
            :error="!!form.errors.value?.rate"
            :error-messages="form.errors.value?.rate"
            :label="t('currency::attributes.currency_rates.rate')"
            type="number"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn color="default" :disabled="form.loading.value" @click="close">
            {{ t('admin::admin.buttons.cancel') }}
          </VBtn>
          <VBtn
            color="primary"
            :disabled="!form.state.rate || form.loading.value"
            :loading="form.loading.value"
            @click="submit"
          >
            {{ t('admin::admin.buttons.update') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VForm>
  </VDialog>
</template>
