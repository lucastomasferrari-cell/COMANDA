<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import BlockInfo from '@/modules/core/components/BlockInfo.vue'

  defineProps<{
    order: Record<string, any>
  }>()
  const { t } = useI18n()
  const { user } = useAuth()

</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('order::orders.show.cards.order_information') }}
    </VCardTitle>
    <VCardText>
      <VRow class="border-b-dashed">
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.reference_no')" :value="order.reference_no" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.order_number')" :value="order.order_number" />
        </VCol>
        <VCol v-if="!user?.assigned_to_branch" cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.branch')" :value="order.branch.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.status')">
            <TableStatus :item="order" />
          </BlockInfo>
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.type')">
            <TableEnum column="type" :item="order" />
          </BlockInfo>
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.payment_status')">
            <TableEnum column="payment_status" :item="order" />
          </BlockInfo>
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.guest_count')" :value="order.guest_count" />
        </VCol>
      </VRow>
      <VRow class="border-b-dashed">
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.created_by')" :value="order.created_by?.name" />
        </VCol>
        <VCol v-if="order.type.id==='dine_in'" cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.table')" :value="order.table?.name" />
        </VCol>
        <VCol v-if="order.waiter" cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.waiter')" :value="order.waiter?.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.cashier')" :value="order.cashier?.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo
            :title="t('order::orders.show.pos_register')"
            :value="order.pos_register?.name"
          />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.pos_session_id')" :value="order.pos_session_id" />
        </VCol>
      </VRow>
      <VRow>
        <VCol v-if="order.type.id==='dine_in'" cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.served_at')" :value="order.served_at" />
        </VCol>
        <VCol v-if="order.type.id==='dine_in'" cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.closed_at')" :value="order.closed_at" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.created_at')" :value="order.created_at" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('order::orders.show.updated_at')" :value="order.updated_at" />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>

</style>
