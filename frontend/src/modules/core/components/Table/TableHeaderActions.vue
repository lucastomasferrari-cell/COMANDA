<script lang="ts" setup>
  import type { TableAction } from '@/modules/core/contracts/Table.ts'
  import { useTableAction } from '@/modules/core/composables/tableAction.ts'

  const props = defineProps<{
    actions: TableAction[]
    leftActions?: TableAction[]
    refresh: () => void
    resource: string
    name: string
    module: string
    itemId: string
  }>()

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
  )

</script>

<template>
  <div class="d-flex justify-space-between align-center gap-2 mb-3">
    <div class="d-flex gap-2">
      <template
        v-for="action in (leftActions || [])"
        :key="action.key"
      >
        <VMenu v-if="isVisible(action) && action.type === 'dropdown'">
          <template #activator="{ props:menuProps }">
            <VBtn
              color="primary"
              :disabled="isDisabled(action)"
              :loading="action.loading"
              v-bind="menuProps"
            >
              <VIcon v-if="getIcon(action)" :icon="getIcon(action)" start />
              {{ getLabel(action) }}
              <VIcon end icon="tabler-chevron-down" />
            </VBtn>
          </template>

          <VList style="max-height: 360px; overflow-y: auto;">
            <VListItem
              v-for="(option, optionIndex) in action.options"
              :key="optionIndex"
              @click="()=>onClick(action,option)"
            >
              <template #prepend>
                <VIcon v-if="option.icon" :icon="String(option.icon)" size="18" />
              </template>
              <VListItemTitle>
                {{ option.name || option.label || option.title || option.id }}
              </VListItemTitle>
              <VListItemSubtitle v-if="option.description">
                {{ option.description }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VMenu>
        <VTooltip
          v-else-if="isVisible(action)"
          :text="action.tooltip || getLabel(action)"
        >
          <template #activator="{ props: tooltipProps }">
            <VBtn
              :color="getColor(action)"
              :disabled="isDisabled(action)"
              :loading="action.loading"
              v-bind="tooltipProps"
              @click="()=>onClick(action)"
            >
              <VIcon v-if="getIcon(action)" :icon="getIcon(action)" start />
              {{ getLabel(action) }}
            </VBtn>
          </template>
        </VTooltip>
      </template>
    </div>

    <div class="d-flex gap-2">
      <template
        v-for="action in actions"
        :key="action.key"
      >
        <VMenu v-if="isVisible(action) && action.type === 'dropdown'">
          <template #activator="{ props:menuProps }">
            <VBtn
              color="primary"
              :disabled="isDisabled(action)"
              :loading="action.loading"
              v-bind="menuProps"
            >
              <VIcon v-if="getIcon(action)" :icon="getIcon(action)" start />
              {{ getLabel(action) }}
              <VIcon end icon="tabler-chevron-down" />
            </VBtn>
          </template>

          <VList style="max-height: 360px; overflow-y: auto;">
            <VListItem
              v-for="(option, optionIndex) in action.options"
              :key="optionIndex"
              @click="()=>onClick(action,option)"
            >
              <template #prepend>
                <VIcon v-if="option.icon" :icon="String(option.icon)" size="18" />
              </template>
              <VListItemTitle>
                {{ option.name || option.label || option.title || option.id }}
              </VListItemTitle>
              <VListItemSubtitle v-if="option.description">
                {{ option.description }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VMenu>
        <VTooltip
          v-else-if="isVisible(action)"
          :text="action.tooltip || getLabel(action)"
        >
          <template #activator="{ props: tooltipProps }">
            <VBtn
              :color="getColor(action)"
              :disabled="isDisabled(action)"
              :loading="action.loading"
              v-bind="tooltipProps"
              @click="()=>onClick(action)"
            >
              <VIcon v-if="getIcon(action)" :icon="getIcon(action)" start />
              {{ getLabel(action) }}
            </VBtn>
          </template>
        </VTooltip>
      </template>
    </div>
  </div>
</template>
