<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import Log from './Log.vue'

  defineProps<{
    statusLogs: Record<string, any>[]
  }>()
  const { t } = useI18n()

</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('order::orders.show.cards.status_logs') }}
    </VCardTitle>
    <VCardText>
      <VTable>
        <thead>
          <tr>
            <th>{{ t('order::orders.show.status') }}</th>
            <th>{{ t('order::orders.show.changed_by') }}</th>
            <th>{{ t('order::orders.show.reason') }}</th>
            <th>{{ t('order::orders.show.note') }}</th>
            <th>{{ t('order::orders.show.changed_at') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in statusLogs" :key="log.id">
            <Log :log="log" />
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
