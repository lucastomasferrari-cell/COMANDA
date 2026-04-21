<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import SessionInfoItem from './SessionInfoItem.vue'

  defineProps<{ item: any }>()
  const { t } = useI18n()
</script>

<template>
  <span class="text-h6 d-block font-weight-bold">
    {{ t('pos::pos_sessions.show.cards.sales_orders_summary.title') }}
  </span>
  <span class="text-body-2 text-medium-emphasis">
    {{ t('pos::pos_sessions.show.cards.sales_orders_summary.description') }}
  </span>
  <div class="d-flex">
    <SessionInfoItem :label="t('pos::pos_sessions.show.total_sales')" :value="item.total_sales?.formatted" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.total_refunds')" :value="item.total_refunds?.formatted" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.orders_count')" :value="item.orders_count" />
  </div>
</template>

<style lang="scss" scoped>

</style>
