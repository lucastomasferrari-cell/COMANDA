<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import SessionInfoItem from './SessionInfoItem.vue'

  defineProps<{ item: any }>()
  const { t } = useI18n()
</script>

<template>
  <span class="text-h6 d-block font-weight-bold">
    {{ t('pos::pos_sessions.show.cards.payment_breakdown.title') }}
  </span>
  <span class="text-body-2 text-medium-emphasis">
    {{ t('pos::pos_sessions.show.cards.payment_breakdown.description') }}
  </span>
  <div class="d-flex">
    <SessionInfoItem :label="t('pos::pos_sessions.show.system_card_sales')" :value="item.system_card_sales?.formatted" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.system_cash_sales')" :value="item.system_cash_sales?.formatted" />
    <SessionInfoItem
      :label="t('pos::pos_sessions.show.system_other_sales')"
      :value="item.system_other_sales?.formatted"
    />
  </div>
</template>

<style lang="scss" scoped>

</style>
