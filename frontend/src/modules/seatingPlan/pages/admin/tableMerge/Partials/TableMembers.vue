<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{ item: any }>()
  const { t } = useI18n()
</script>

<template>
  <div class="gap-2 d-flex align-center px-3">
    <template v-for="member in item.members" :key="member.id">
      <VTooltip v-if="member.is_main">
        <template #activator="{ props }">
          <VChip color="primary" size="small" v-bind="props">
            <VIcon icon="tabler-rosette-discount-check" />&nbsp;
            {{ member.table.name }}
          </VChip>
        </template>
        <span>{{ t(`seatingplan::tables.primary_table`) }}</span>
      </VTooltip>
      <VChip v-else color="default" size="small">
        {{ member.table.name }}
      </VChip>
    </template>
  </div>
</template>
