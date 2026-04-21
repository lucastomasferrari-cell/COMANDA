<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import SessionInfoItem from './SessionInfoItem.vue'

  defineProps<{ item: any }>()
  const { t } = useI18n()
</script>

<template>
  <span class="text-h6 d-block font-weight-bold">
    {{ t('pos::pos_sessions.show.cards.session_information.title') }}
  </span>
  <span class="text-body-2 text-medium-emphasis">
    {{ t('pos::pos_sessions.show.cards.session_information.description') }}
  </span>

  <div class="d-flex flex-wrap">
    <SessionInfoItem :label="t('pos::pos_sessions.show.pos_register')" :value="item.pos_register.name" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.opened_by')" :value="item.opened_by.name" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.closed_by')" :value="item.closed_by?.name" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.status')">
      <TableStatus :item="item" />
    </SessionInfoItem>
    <SessionInfoItem :label="t('pos::pos_sessions.show.opened_at')" :value="item.opened_at" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.closed_at')" :value="item.closed_at" />
    <SessionInfoItem :label="t('pos::pos_sessions.show.notes')" :value="item.notes" />
  </div>
</template>
