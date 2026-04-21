<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import Payment from './Payment.vue'

  defineProps<{
    payments: Record<string, any>[]
  }>()
  const { t } = useI18n()

</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('order::orders.show.cards.payments') }}
    </VCardTitle>
    <VCardText>
      <span v-if="payments.length === 0" class="text-center d-block">
        {{ t('order::orders.show.no_data_available') }}
      </span>
      <template v-else>
        <Payment
          v-for="(payment, index) in payments"
          :key="payment.id"
          :border="payments.length-1!=index"
          :payment="payment"
        />
      </template>
    </VCardText>
  </VCard>
</template>
