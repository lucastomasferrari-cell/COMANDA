<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import BlockInfo from '@/modules/core/components/BlockInfo.vue'

  defineProps<{
    customer: Record<string, any>
  }>()
  const { t } = useI18n()

</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('order::orders.show.cards.customer_information') }}
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.name')" :value="customer.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.phone')" :value="customer.phone" />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
