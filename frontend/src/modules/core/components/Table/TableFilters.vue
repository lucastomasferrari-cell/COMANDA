<script lang="ts" setup>
  import type { TableFilterSchema } from '@/modules/core/contracts/Table.ts'
  import { format } from 'date-fns'
  import { cloneDeep, debounce } from 'lodash'
  import { useI18n } from 'vue-i18n'

  const props = withDefaults(
    defineProps<{
      filterSchema?: TableFilterSchema[]
      loading: boolean
    }>(),
    {
      filterSchema: () => [],
    })
  const emit = defineEmits(['apply', 'reload'])

  const { t } = useI18n()
  const filters = ref<Record<string, any>>({})
  const initialFilters = ref<Record<string, any>>({})
  const showFilter = ref(false)
  const isFirstRun = ref(true)

  function applyFilters () {
    emit('apply', filters.value)
  }

  function reloadFilters () {
    emit('reload', true)
  }

  function reset () {
    filters.value = cloneDeep(initialFilters.value)
  }

  const isResetDisabled = computed(() => {
    return Object.values(filters.value).every(v => {
      return v === null || v === '' || v === false || (Array.isArray(v) && v.length === 0)
    })
  })

  onMounted(() => {
    for (const field of props.filterSchema) {
      filters.value[field.key] = field.default ?? null
      if (field.depends) {
        watch(() => filters.value[field.depends!], (newVal, oldVal) => {
          if (newVal !== oldVal) {
            filters.value[field.key] = field.default ?? null
            reloadFilters()
          }
        })
      }
    }

    initialFilters.value = cloneDeep(filters.value)
  })

  const debouncedApplyFilters = debounce(applyFilters, 300)

  watch(
    filters,
    () => {
      if (isFirstRun.value) {
        isFirstRun.value = false
        return
      }
      debouncedApplyFilters()
    },
    { deep: true },
  )

  const activeFilterCount = computed(() => {
    return Object.values(filters.value).filter(v => {
      return v !== null && v !== '' && v !== false && !(Array.isArray(v) && v.length === 0)
    }).length
  })

</script>

<template>
  <div class="mx-1 filters-container">
    <VMenu
      v-model="showFilter"
      :close-on-content-click="false"
      location="bottom"
      offset-y
    >
      <template #activator="{ props:menuProps }">
        <VBadge
          class="custom-primary-badge"
          :content="activeFilterCount"
          :model-value="activeFilterCount > 0"

          offset-y="-2"
        >
          <VIcon
            class="text-medium-emphasis cursor-pointer"
            :disabled="loading"
            icon="tabler-filter-filled"
            v-bind="menuProps"
          />
        </VBadge>
      </template>
      <VForm @submit.prevent="applyFilters">
        <VCard class="pa-4" width="320">
          <div class="d-flex justify-space-between align-center w-100 mb-2">
            <p class="font-weight-bold mb-0">{{ t('admin::admin.filters_title') }}</p>
            <VBtn
              color="error"
              :disabled="loading||isResetDisabled"
              :loading="loading"
              size="small"
              variant="text"
              @click="reset"
            >
              {{ t('admin::admin.buttons.reset') }}
            </VBtn>
          </div>

          <VRow>

            <VCol
              v-for="field in filterSchema"
              :key="field.key"
              cols="12"
            >
              <VTextField
                v-if="field.type === 'text'"
                v-model="filters[field.key]"
                :clearable="field.clearable !== false"
                dense
                :label="field.label"
                :placeholder="field.placeholder"
              />
              <VSelect
                v-else-if="field.type === 'select'"
                v-model="filters[field.key]"
                :chips="field.multiple"
                :clearable="field.clearable !== false"
                dense
                item-title="name"
                item-value="id"
                :items="field.options"
                :label="field.label"
                :multiple="field.multiple"
                :placeholder="field.placeholder"
              />
              <VCheckbox
                v-else-if="field.type === 'checkbox'"
                v-model="filters[field.key]"
                density="compact"
                :label="field.label"
              />
              <VTextField
                v-else-if="field.type === 'number'"
                v-model="filters[field.key]"
                :clearable="field.clearable !== false"
                dense
                :label="field.label"
                type="number"
              />
              <VMenu
                v-else-if="field.type === 'date'"
                v-model="field.menu"
                :close-on-content-click="false"
                offset-y
                transition="scale-transition"
              >
                <template #activator="{ props:menuActivatorProps }">
                  <VTextField
                    v-model="filters[field.key]"
                    :clearable="field.clearable !== false"
                    :label="field.label"
                    :placeholder="field.placeholder"
                    prepend-inner-icon="tabler-calendar"
                    readonly
                    v-bind="menuActivatorProps"
                  />
                </template>
                <VDatePicker
                  v-model="filters[field.key]"
                  color="primary"
                  :max="field.max ?? new Date().toLocaleDateString('en-CA')"
                  :min="field.min"
                  show-adjacent-months
                  @update:model-value="val => {
                    filters[field.key] = format(new Date(val), 'yyyy-MM-dd')
                    field.menu = false
                  }"
                />
              </VMenu>
            </VCol>
          </VRow>
        </VCard>
      </VForm>
    </VMenu>
  </div>
</template>

<style lang="scss" scoped>
.filters-container {
  display: flex;

  .custom-primary-badge .v-badge__badge {
    background-color: rgba(var(--v-theme-primary), 0.14) !important;
    color: rgb(var(--v-theme-primary)) !important;
    font-size: 12px;
    border-radius: 8px;
    padding: 0 6px;
    height: 18px;
    min-width: 18px;
    font-weight: 500;
  }
}

</style>
