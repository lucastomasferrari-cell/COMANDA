<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    merge: Record<string, any> | null
    currentTableId: number
  }>()

  const emit = defineEmits<{
    (e: 'reload', tableId: number): void
  }>()

  const { t } = useI18n()
</script>

<template>
  <div v-if="merge" class="border-b-dashed pb-3 mt-2">
    <div class="d-flex mb-3 align-center justify-space-between font-weight-bold text-h6">
      <div class="d-flex align-center gap-1">
        <VIcon color="info" icon="tabler-arrow-merge" size="17" />
        {{ t('seatingplan::tables.merge_details') }}
      </div>
      <TableEnum column="type" :item="merge" />
    </div>
    <div class="gap-2 d-flex align-center px-3">
      <template v-for="member in merge.members" :key="member.id">
        <VTooltip v-if="member.is_main">
          <template #activator="{ props }">
            <VChip
              :color="currentTableId == member.table.id? 'primary':'default'"
              v-bind="props"
              @click="emit('reload',member.table.id)"
            >
              <VIcon icon="tabler-rosette-discount-check" />&nbsp;
              {{ member.table.name }}
            </VChip>
          </template>
          <span>{{ t(`seatingplan::tables.primary_table`) }}</span>
        </VTooltip>
        <VChip
          v-else
          :color="currentTableId == member.table.id? 'primary':'default'"
          @click="emit('reload',member.table.id)"
        >
          {{ member.table.name }}
        </VChip>
      </template>
    </div>
  </div>
</template>
