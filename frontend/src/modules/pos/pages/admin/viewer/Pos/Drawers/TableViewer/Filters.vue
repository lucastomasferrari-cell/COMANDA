<script lang="ts" setup>
  import { computed, ref, toRef } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    disabled: boolean
    filters: Record<string, any>
    meta: Record<string, any>
  }>()

  const { t } = useI18n()

  const search = toRef(props.filters, 'search')
  const floors = toRef(props.filters, 'floors')
  const zones = toRef(props.filters, 'zones')
  const statuses = toRef(props.filters, 'statuses')

  if (!search.value) search.value = ''
  if (!floors.value) floors.value = []
  if (!zones.value) zones.value = []
  if (!statuses.value) statuses.value = []
  const metaFloors = computed(() => props.meta?.floors ?? [])
  const metaZones = computed(() => props.meta?.zones ?? [])
  const metaStatuses = computed(() => props.meta?.statuses ?? [])

  const zonesFiltered = computed(() => {
    if (floors.value.length === 0) return metaZones.value
    return metaZones.value.filter((zone: Record<string, any>) => floors.value.includes(zone.floor_id))
  })

  watch(() => floors.value, () => {
    zones.value = []
  })

</script>

<template>
  <div class="mt-3 d-flex align-center ga-2">
    <VTextField
      v-model="search"
      class="flex-grow-1"
      clearable
      :label="t('admin::admin.table.search_placeholder')"
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
          <template v-if="metaStatuses.length > 0">
            <VListItem :title="t('pos::pos_viewer.table_statuses')" />
            <VChipGroup
              v-model="statuses"
              class="px-3 pb-2"
              color="secondary"
              column
              :disabled="disabled"
              multiple
            >
              <VChip v-for="status in metaStatuses" :key="status.id" :value="status.id">
                {{ status.name }}
              </VChip>
            </VChipGroup>
            <VDivider />
          </template>

          <template v-if="metaFloors.length > 0">
            <VListItem :title="t('pos::pos_viewer.floors')" />
            <VChipGroup
              v-model="floors"
              class="px-3 pb-2"
              color="primary"
              column
              :disabled="disabled"
              multiple
            >
              <VChip v-for="floor in metaFloors" :key="floor.id" :value="floor.id">
                {{ floor.name }}
              </VChip>
            </VChipGroup>
            <VDivider />
          </template>

          <template v-if="zonesFiltered.length > 0">
            <VListItem :title="t('pos::pos_viewer.zones')" />
            <VChipGroup
              v-model="zones"
              class="px-3 pb-2"
              color="info"
              column
              :disabled="disabled"
              multiple
            >
              <VChip
                v-for="zone in zonesFiltered"
                :key="zone.id"
                :value="zone.id"
              >
                {{ zone.name }}
              </VChip>
            </VChipGroup>
          </template>
        </VList>
      </VCard>
    </v-menu>
  </div>
</template>
