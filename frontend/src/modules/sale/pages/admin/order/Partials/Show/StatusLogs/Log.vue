<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    log: Record<string, any>
  }>()

  const { t } = useI18n()
</script>

<template>
  <td>
    <TableStatus :item="log" />
  </td>
  <td>
    {{ log.changed_by?.name || t('order::orders.show.system') }}
  </td>
  <td>
    {{ log.reason?.name || "-" }}
  </td>
  <td>
    {{ log.note || "-" }}
  </td>
  <td>
    {{ log.created_at }}
  </td>
</template>
