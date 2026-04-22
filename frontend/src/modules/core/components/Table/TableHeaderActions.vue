<script lang="ts" setup>
  import type { TableAction } from '@/modules/core/contracts/Table.ts'
  import type { Ref } from 'vue'
  import { computed, inject } from 'vue'
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

  // Si estamos dentro de un PageTabs, teletransportamos las actions al
  // row de tabs en vez de renderizar nuestro propio row inline.
  // Evita tabs + botón en dos renglones separados.
  const pageTabsTarget = inject<Ref<string> | null>('pageTabsActionsTarget', null)
  const teleportTarget = computed(() => pageTabsTarget?.value ?? null)
  const hasLeftActions = computed(() => (props.leftActions?.length ?? 0) > 0)
  const hasActions = computed(() => (props.actions?.length ?? 0) > 0)
</script>

<template>
  <!-- Teleport cuando estamos dentro de un PageTabs. Solo viaja el
       grupo de actions principales (derecha). Los leftActions —si los
       hay— siguen inline porque típicamente son filtros contextuales. -->
  <Teleport v-if="teleportTarget && hasActions" :to="teleportTarget">
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
  </Teleport>

  <!-- Fila inline: se renderiza cuando no hay PageTabs padre, o cuando
       hay leftActions (los filtros quedan inline siempre). Si ambos
       grupos están vacíos, no muestra nada. -->
  <div
    v-if="hasLeftActions || (!teleportTarget && hasActions)"
    class="d-flex justify-space-between align-center gap-2 mb-3"
  >
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

    <div v-if="!teleportTarget" class="d-flex gap-2">
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
