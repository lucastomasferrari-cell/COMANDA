<script lang="ts" setup>
  import type { TableAction, TableFilterSchema } from '@/modules/core/contracts/Table.ts'
  import { debounce } from 'lodash'
  import { ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import DropdownBulkActions from '@/modules/core/components/Table/DropdownBulkActions.vue'

  const props = defineProps<{
    modelValue: string | null
    disabled: boolean
    hasSearch: boolean
    paddingTop?: number
    loading: boolean
    title?: string
    bulkActions?: TableAction[]
    filters?: TableFilterSchema[]
    selected: string[] | number[]
    refresh: () => void
    resource: string
    name: string
    module: string
    itemId: string
    deselectAll: () => void
  }>()

  const emit = defineEmits(['update:modelValue', 'apply:filters', 'reload:filters'])
  const { t } = useI18n()

  const searchQuery = ref(props.modelValue)

  const emitSearch = debounce((val: string) => {
    emit('update:modelValue', val)
  }, 300)

  watch(searchQuery, val => emitSearch(val ?? ''))

  function onApplyFilters (filterValues: Record<string, unknown>) {
    emit('apply:filters', filterValues)
  }

  function onReloadFilters (value: unknown) {
    emit('reload:filters', value)
  }

</script>

<template>
  <VRow class="align-center pa-4" :class="[`pt-${paddingTop}`]">
    <VCol class="d-flex align-center gap-2" cols="12" md="6">
      <DropdownBulkActions
        v-if="bulkActions && bulkActions?.length>0 && selected.length>0"
        :bulk-actions="bulkActions"
        :item-id="itemId"
        :module="module"
        :name="name"
        :refresh="refresh"
        :resource="resource"
        :selected="selected"
      />
      <VCardTitle v-if="title" class="pa-0">{{ title }}</VCardTitle>
    </VCol>

    <VCol class="d-flex justify-end align-center" cols="12" md="6">
      <VTextField
        v-if="hasSearch"
        v-model="searchQuery"
        class="w-100"
        clearable
        density="compact"
        :placeholder="t('admin::admin.table.search_placeholder')"
        prepend-inner-icon="tabler-search"
        style="max-width: 300px"
      />
      <TableFilters
        v-if="filters && filters.length > 0"
        :filter-schema="filters"
        :loading="loading"
        @apply="onApplyFilters"
        @reload="onReloadFilters"
      />
    </VCol>
  </VRow>
  <div v-if="selected.length>0" class="selected-info-container">
    <span>{{ t('admin::admin.table.number_records_selected', {number: selected.length}) }}</span>
    <div>
      <VBtn color="error" size="small" variant="text" @click="deselectAll">
        {{ t("admin::admin.buttons.deselect_all") }}
      </VBtn>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.selected-info-container {
  background-color: rgb(var(--v-table-head-bg));
  color: rgb(var(--v-on-table-head-bg));
  padding: 3px 20px;
  border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  display: flex;
  justify-content: space-between;
  align-items: center;
}

</style>
