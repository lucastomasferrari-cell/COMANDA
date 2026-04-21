<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import Money from '@/modules/core/components/Money.vue'
  import Row from './Row.vue'

  defineProps<{
    order: Record<string, any>
  }>()
  const { t } = useI18n()

  const { user, can } = useAuth()

</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('order::orders.show.cards.order_summary') }}
    </VCardTitle>
    <VCardText>
      <template v-if="!user?.assigned_to_branch">
        <Row :label="t('order::orders.show.currency')" :value="order.currency" />
        <Row border :label="t('order::orders.show.currency_rate')" :value="order.currency_rate" />
      </template>
      <template v-if="can('admin.orders.financials')">
        <Row class="pt-2" :label="t('order::orders.show.cost_price')">
          <Money :money="order.cost_price" />
        </Row>
        <Row border :label="t('order::orders.show.revenue')">
          <Money :money="order.revenue" />
        </Row>
      </template>
      <Row
        :border="order.taxes.length === 0"
        class="pt-2"
        :label="t('order::orders.show.subtotal')"
      >
        <Money :money="order.subtotal" />
      </Row>
      <Row
        v-if="order.discount"
        class="pt-2"
        :label="t('order::orders.show.discount') +` (${order.discount.name})`"
      >
        <Money :classes="['text-success']" :money="order.discount.amount" prefix="-" />
      </Row>
      <Row
        v-for="(tax,index) in order.taxes"
        :key="tax.id"
        :border="order.taxes.length-1 === index"
        class="pt-2"
        :label="tax.name"
      >
        <Money :money="tax.amount" />
      </Row>
      <Row class="pt-2" :label="t('order::orders.show.total')" primary>
        <Money :money="order.total" />
      </Row>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>

</style>
