<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import Product from './Product.vue'

  defineProps<{
    products: Record<string, any>[]
  }>()
  const { t } = useI18n()
  const { can } = useAuth()
</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('order::orders.show.cards.products') }}
    </VCardTitle>
    <VCardText>
      <VTable>
        <thead>
          <tr>
            <th style="width: 30%">{{ t('order::orders.show.product') }}</th>
            <th>{{ t('order::orders.show.status') }}</th>
            <th>{{ t('order::orders.show.unit_price') }}</th>
            <th>{{ t('order::orders.show.quantity') }}</th>
            <th>{{ t('order::orders.show.subtotal') }}</th>
            <th>{{ t('order::orders.show.tax') }}</th>
            <th>{{ t('order::orders.show.total') }}</th>
            <template v-if="can('admin.orders.financials')">
              <th>{{ t('order::orders.show.cost_price') }}</th>
              <th>{{ t('order::orders.show.revenue') }}</th>
            </template>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <Product :product="product" />
          </tr>
        </tbody>
      </VTable>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
thead th {
  text-transform: none;
  font-weight: 700;
}
</style>
