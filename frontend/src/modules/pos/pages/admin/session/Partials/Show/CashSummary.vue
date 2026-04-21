<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import SessionInfoItem from './SessionInfoItem.vue'

  defineProps<{ item: any }>()
  const { t } = useI18n()
</script>

<template>
  <span class="text-h6 d-block font-weight-bold">
    {{ t('pos::pos_sessions.show.cards.cash_summary.title') }}
  </span>
  <span class="text-body-2 text-medium-emphasis">
    {{ t('pos::pos_sessions.show.cards.cash_summary.description') }}
  </span>

  <div class="d-flex">
    <SessionInfoItem :label="t('pos::pos_sessions.show.opening_float')" :value="item.opening_float?.formatted" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.declared_cash')" :value="item.declared_cash?.formatted" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.system_cash_sales')" :value="item.system_cash_sales?.formatted" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.cash_over_short')" :value="item.cash_over_short?.formatted" />
  </div>
</template>

<style lang="scss" scoped>

</style>
