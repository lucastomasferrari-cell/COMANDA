<script lang="ts" setup>
  import { ref, toRefs } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    disabled: boolean
    filters: Record<string, any>
    meta: Record<string, any>
  }>()

  const { t } = useI18n()

  const { search, types, statuses, paymentStatuses } = toRefs(props.filters)
  const searchInputRef = ref<any>(null)

  // El padre (OrdersDrawer) llama focus() al abrir, para que el cajero
  // tipee directo sin click intermedio.
  defineExpose({
    focus: () => searchInputRef.value?.focus?.(),
  })
</script>

<template>
  <div class="mt-3 d-flex align-center ga-2">
    <VTextField
      ref="searchInputRef"
      v-model="search"
      class="flex-grow-1"
      clearable
      :placeholder="t('pos::pos_viewer.search_by_order_number')"
      prepend-inner-icon="tabler-search"
      :readonly="disabled"
    />

    <v-menu :close-on-content-click="false">
      <template #activator="{ props:menuProps }">
        <v-btn
          class="ml-2"
          :disabled="disabled"
          prepend-icon="tabler-filter"
          v-bind="menuProps"
          variant="tonal"
        >
          {{ t('pos::pos_viewer.filters') }}
        </v-btn>
      </template>

      <VCard width="300">
        <VList>

          <template v-if="meta.orderTypes.length > 0">
            <VListItem :title="t('pos::pos_viewer.order_types')" />
            <VChipGroup
              v-model="types"
              class="px-3 pb-2"
              color="primary"
              column
              :disabled="disabled"
              multiple
            >
              <VChip v-for="type in meta.orderTypes" :key="type.id" :value="type.id">
                {{ type.name }}
              </VChip>
            </VChipGroup>
            <VDivider />
          </template>

          <template v-if="meta.statuses.length > 0">
            <VListItem :title="t('pos::pos_viewer.order_statuses')" />
            <VChipGroup
              v-model="statuses"
              class="px-3 pb-2"
              color="secondary"
              column
              :disabled="disabled"
              multiple
            >
              <VChip v-for="status in meta.statuses" :key="status.id" :value="status.id">
                {{ status.name }}
              </VChip>
            </VChipGroup>
            <VDivider />
          </template>

          <template v-if="meta.paymentStatuses.length > 0">
            <VListItem :title="t('pos::pos_viewer.payment_statuses')" />
            <VChipGroup
              v-model="paymentStatuses"
              class="px-3 pb-2"
              color="info"
              column
              :disabled="disabled"
              multiple
            >
              <VChip
                v-for="paymentStatus in meta.paymentStatuses"
                :key="paymentStatus.id"
                :value="paymentStatus.id"
              >
                {{ paymentStatus.name }}
              </VChip>
            </VChipGroup>
          </template>
        </VList>
      </VCard>
    </v-menu>
  </div>
</template>
