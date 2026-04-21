<script lang="ts" setup>
  import type { Ref } from 'vue'
  import { toRef } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    meta: Record<string, any>
    form: Record<string, any>
  }>()

  defineEmits<{
    (e: 'add-payment'): void
    (e: 'set-active-input', path: string): void
  }>()

  const { t } = useI18n()
  const payments = toRef(props.form.state as Record<string, any>, 'payments') as Ref<Record<string, any>[]>

  if (!payments.value) {
    payments.value = []
  }

  function removePayment (index: number) {
    payments.value.splice(index, 1)
  }

  function updatePaymentMethod (payment: Record<string, any>, method: string | null) {
    if (method === 'gift_card') {
      payment.transaction_id = null
    } else {
      payment.gift_card_code = null
    }
  }

</script>

<template>
  <h4>{{ t('order::orders.payments') }}</h4>
  <div class="mt-4">
    <VRow v-for="(payment,index) in payments" :key="index" dense>
      <VCol cols="12" md="3">
        <VSelect
          v-model="payment.method"
          @update:model-value="updatePaymentMethod(payment, $event)"
          :error="!!form.errors.value?.[`payments.${index}.method`]"
          :error-messages="form.errors.value?.[`payments.${index}.method`]"
          item-title="name"
          item-value="id"
          :items="meta.paymentMethods"
          :label="t('order::attributes.payments.payments.*.method')"
          :readonly="form.loading.value"
        />
      </VCol>
      <VCol cols="12" md="3">
        <VTextField
          v-model="payment.amount"
          v-decimal-en
          :error="!!form.errors.value?.[`payments.${index}.amount`]"
          :error-messages="form.errors.value?.[`payments.${index}.amount`]"
          :label="t('order::attributes.payments.payments.*.amount')"
          :prefix="meta.order.currency"
          :readonly="form.loading.value"
          @focus="$emit('set-active-input', `payments.${index}.amount`)"
        />
      </VCol>
      <VCol v-if="payment.method !== 'gift_card'" cols="12" md="3">
        <VTextField
          v-model="payment.transaction_id"
          :error="!!form.errors.value?.[`payments.${index}.transaction_id`]"
          :error-messages="form.errors.value?.[`payments.${index}.transaction_id`]"
          :label="t('order::attributes.payments.payments.*.transaction_id')"
          :readonly="form.loading.value"
        />
      </VCol>
      <VCol v-if="payment.method === 'gift_card'" cols="12" md="3">
        <VTextField
          v-model="payment.gift_card_code"
          :error="!!form.errors.value?.[`payments.${index}.gift_card_code`]"
          :error-messages="form.errors.value?.[`payments.${index}.gift_card_code`]"
          :label="t('order::attributes.payments.payments.*.gift_card_code')"
          :readonly="form.loading.value"
        />
      </VCol>
      <VCol class="d-flex ga-2" cols="12" :md="payment.method === 'gift_card' ? 3 : 3">
        <VBtn
          color="error"
          :disabled="form.loading.value||payments.length == 1"
          icon="tabler-trash"
          rounded
          variant="tonal"
          @click="removePayment(index)"
        />
        <VBtn
          v-if="(payments.length -1) == index"
          color="secondary"
          :disabled="form.loading.value"
          icon="tabler-plus"
          rounded
          variant="tonal"
          @click="$emit('add-payment')"
        />
      </VCol>
    </VRow>
  </div>
</template>
