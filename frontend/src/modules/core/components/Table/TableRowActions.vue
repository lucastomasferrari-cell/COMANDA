<script lang="ts" setup>
  import type { TableAction } from '@/modules/core/contracts/Table.ts'
  import { useTableAction } from '@/modules/core/composables/tableAction.ts'

  const props = defineProps<{
    actions: TableAction[]
    resource: string
    name: string
    module: string
    item: any
    itemId: string
    refresh: () => void
  }>()

  const {
    isVisible,
    isDisabled,
    getIcon,
    getLabel,
    onClick,
    getColor,
  } = useTableAction(
    props.resource,
    props.name,
    props.module,
    props.itemId,
    props.refresh,
  )
</script>

<template>
  <VMenu location="bottom end" offset-y>
    <template #activator="{ props: menuProps }">
      <VBtn color="default" icon size="32" v-bind="menuProps">
        <VIcon icon="tabler-dots-vertical" size="20" />
      </VBtn>
    </template>

    <VList>
      <template
        v-for="action in actions"
        :key="action.key"
      >
        <VListItem
          v-if="isVisible(action, item)"
          :disabled="isDisabled(action, item)"
          @click="() => onClick(action, item)"
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

<style lang="scss" scoped>
.table-row-action-button {
  width: 32px !important;
  height: 32px !important;
  border-radius: 8px !important;
}
</style>
