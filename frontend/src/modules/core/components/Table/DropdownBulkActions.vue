<script lang="ts" setup>
  import type { TableAction } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useTableAction } from '@/modules/core/composables/tableAction.ts'

  const props = defineProps<{
    bulkActions?: TableAction[]
    selected: string[] | number[]
    refresh: () => void
    resource: string
    name: string
    module: string
    itemId: string
  }>()

  const { t } = useI18n()

  const {
    isVisible,
    isDisabled,
    onClick,
    getLabel,
    getColor,
    getIcon,
  } = useTableAction(
    props.resource,
    props.name,
    props.module,
    props.itemId,
    props.refresh,
    true,
  )
</script>

<template>
  <VMenu>
    <template #activator="{ props }">
      <VBtn
        color="grey-darken-1"
        size="small"
        v-bind="props"
        variant="tonal"
      >
        <VIcon icon="tabler-dots-vertical" start />
        {{ t('admin::admin.table.bulk_actions') }}
      </VBtn>
    </template>

    <VList>
      <template v-for="action in bulkActions" :key="action.key">
        <VListItem
          v-if="isVisible(action,selected)"
          :disabled="isDisabled(action,selected)"
          @click="() => onClick(action, selected)"
        >
          <template #prepend>
            <VIcon :color="getColor(action)" :icon="getIcon(action)" size="18" />
          </template>
          <VListItemTitle>
            {{ getLabel(action) }}
          </VListItemTitle>
        </VListItem>
      </template>
    </VList>
  </VMenu>
</template>
