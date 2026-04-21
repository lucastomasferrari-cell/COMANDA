<script lang="ts" setup>
  import type {
    PaginationMeta,
    TableAction,
    TableFilterSchema,
    TableHeader,
  } from '@/modules/core/contracts/Table.ts'
  import { computed, onBeforeUnmount, onMounted, ref, toRefs } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { VDataTableServer } from 'vuetify/components'
  import { http } from '@/modules/core/api/http.ts'
  import TableActiveStatus from '@/modules/core/components/Table/Partials/TableActiveStatus.vue'
  import TableAgent from '@/modules/core/components/Table/Partials/TableAgent.vue'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'
  import TableStatus from '@/modules/core/components/Table/Partials/TableStatus.vue'
  import TableThumbnail from '@/modules/core/components/Table/Partials/TableThumbnail.vue'
  import TableUserInfo from '@/modules/core/components/Table/Partials/TableUserInfo.vue'
  import TableHeaderActions from '@/modules/core/components/Table/TableHeaderActions.vue'
  import TablePagination from '@/modules/core/components/Table/TablePagination.vue'
  import TableRowActions from '@/modules/core/components/Table/TableRowActions.vue'
  import { useTableAction } from '@/modules/core/composables/tableAction.ts'
  import { useReport } from '@/modules/report/composables/report.ts'

  interface SortOption {
    key: string
    order: 'asc' | 'desc'
  }

  const props = withDefaults(defineProps<{
    headers?: TableHeader[]
    resource: string
    name: string
    module: string
    apiUri: string
    title?: string
    hasSearch?: boolean
    hasPagination?: boolean
    paddingTop?: number
    cellComponents?: Record<string, any>
    itemId?: string
    actions?: TableAction[]
    headerActions?: TableAction[]
    leftHeaderActions?: TableAction[]
    bulkActions?: TableAction[]
    defaultFilters?: Record<string, any>
    exceptFilters?: string[]
    onRowClick?: ((event: MouseEvent, value: any) => void) | null | undefined
  }>(), {
    itemId: 'id',
    headers: () => [],
    hasFilters: false,
    actions: () => [],
    headerActions: () => [],
    leftHeaderActions: () => [],
    paddingTop: 4,
    hasSearch: true,
    hasPagination: true,
    defaultFilters: () => ({} as Record<string, any>),
  })

  const { t } = useI18n()
  const toast = useToast()
  const { downloadExportFile } = useReport()
  const { hasPermission } = useTableAction(
    props.resource,
    props.name,
    props.module,
    props.itemId,
    () => {},
  )

  const loading = ref<boolean>(false)
  const pagination = ref<PaginationMeta>({
    current_page: 1,
    from: 1,
    last_page: 1,
    per_page: 10,
    to: 0,
    total: 0,
  })
  const items = ref<any[]>([])
  const defaultFiltersResponse = ref<Record<string, any>>({})
  const { headers } = toRefs(props)
  const sortBy = ref<SortOption[]>([])
  const page = ref(pagination.value.current_page)
  const perPage = ref(pagination.value.per_page)
  const filters = ref<TableFilterSchema[]>([])
  const filtersData = ref<Record<string, any>>({})
  const withFilters = ref<boolean>(true)
  const search = ref('')
  const selected = ref<number[] | string[]>([])
  const finalDefaultFilters = computed(() => ({
    ...props.defaultFilters,
    ...defaultFiltersResponse.value,
  }))
  const apiHeaderActions = ref<TableAction[]>([])
  const hasSearch = ref(props.hasSearch)
  let requestController: AbortController | null = null
  let lastRequestKey = ''

  async function loadData (options?: {
    page?: number
    sortBy?: SortOption[]
    perPage?: number | null
    search?: string | null
    filterValues?: Record<string, any>
  }) {
    const normalizedOptions = {
      page: options?.page ?? page.value,
      sortBy: options?.sortBy ?? sortBy.value,
      perPage: options?.perPage ?? perPage.value,
      search: options?.search ?? search.value,
      filterValues: options?.filterValues ?? filtersData.value,
    }
    const requestKey = JSON.stringify(normalizedOptions)

    if (loading.value && requestKey === lastRequestKey) {
      return
    }

    requestController?.abort()
    const controller = new AbortController()
    requestController = controller
    lastRequestKey = requestKey
    loading.value = true

    try {
      const response = await http.get(props.apiUri, {
        signal: controller.signal,
        params: {
          with_filters: withFilters.value ? 1 : 0,
          page: normalizedOptions.page,
          sorts: normalizedOptions.sortBy,
          per_page: normalizedOptions.perPage,
          filters: {
            search: normalizedOptions.search,
            ...finalDefaultFilters.value,
            ...normalizedOptions.filterValues,
          },
        },
      })
      items.value = response.data.body.data
      defaultFiltersResponse.value = response.data.body.default_filters || {}

      if (props.hasPagination) {
        pagination.value = response.data.body.pagination
      }

      if (response.data.body.has_search) {
        hasSearch.value = true
      }

      if (headers.value.length === 0 && response.data.body.headers && response.data.body.headers.length > 0) {
        for (const header of response.data.body.headers) {
          headers.value.push(header)
        }
        if (response.data.body.export_methods && response.data.body.export_methods.length > 0) {
          const exportApiUri = response.data.body.export_api_uri as string | undefined
          const exportPermission = response.data.body.export_permission as string | undefined
          apiHeaderActions.value.push({
            key: 'export',
            label: t('admin::admin.buttons.export'),
            permission: exportPermission || `admin.reports.${response.data.body.key}`,
            options: response.data.body.export_methods,
            icon: 'tabler-database-export',
            type: 'dropdown' as const,
            onClick: async option => {
              if (!exportApiUri) {
                await downloadExportFile(
                  response.data.body.key,
                  option.id,
                  { search: search.value, ...filtersData.value },
                )
                return
              }

              const responseFile = await http.get(`${exportApiUri}/${option.id}`, {
                params: {
                  filters: { search: search.value, ...filtersData.value },
                  sorts: sortBy.value,
                },
                responseType: 'blob',
              })

              const blob = responseFile.data
              const filename = `${props.name}-${new Date().toISOString().slice(0, 10)}.${option.id}`
              const url = window.URL.createObjectURL(blob)
              const link = document.createElement('a')
              link.href = url
              link.setAttribute('download', filename)
              document.body.append(link)
              link.click()
              link.remove()
              window.URL.revokeObjectURL(url)
            },
          })
        }
      }

      if (withFilters.value) {
        withFilters.value = false
        const filterList = response.data.body?.filters || []
        const filteredList = props.exceptFilters && props.exceptFilters.length > 0
          ? filterList.filter((filter: Record<string, any>) => !props.exceptFilters?.includes(filter.key))
          : filterList
        const defaultFilters = (finalDefaultFilters.value || {}) as Record<string, any>

        filters.value = filteredList.map((filter: Record<string, any>) => ({
          ...filter,
          default: defaultFilters[filter.key] ?? filter.default ?? null,
        }))
      }
    } catch (error: any) {
      if (error?.code === 'ERR_CANCELED' || error?.name === 'CanceledError' || error?.name === 'AbortError') {
        return
      }

      toast.error(t('core::errors.failed_to_load_data'))
    } finally {
      if (requestController === controller) {
        requestController = null
        loading.value = false
      }
    }
  }

  const { pause, resume } = pausableWatch(
    [perPage, page, sortBy],
    ([newPerPage], [oldPerPage]) => {
      if (newPerPage === oldPerPage) {
        loadData({
          page: page.value,
          perPage: perPage.value,
          sortBy: sortBy.value,
          search: search.value,
          filterValues: filtersData.value,
        })
      } else {
        pause()
        page.value = 1
        requestAnimationFrame(() => {
          resume()
          loadData({
            page: page.value,
            perPage: perPage.value,
            sortBy: sortBy.value,
            search: search.value,
            filterValues: filtersData.value,
          })
        })
      }
    },
  )

  debouncedWatch(search, () => {
    if (page.value !== 1) {
      page.value = 1
      return
    }

    loadData({
      page: 1,
      perPage: perPage.value,
      sortBy: sortBy.value,
      search: search.value,
      filterValues: filtersData.value,
    })
  }, { debounce: 300, maxWait: 800 })

  function handleSortByUpdate (val: { key: string, order: 'asc' | 'desc' }[]) {
    sortBy.value = val.map(sort => {
      const header = headers.value.find(h => h.value === sort.key)
      return {
        key: header?.sortable_key || sort.key,
        order: sort.order,
      }
    })
  }

  onMounted(() => {
    loadData()
  })

  onBeforeUnmount(() => {
    requestController?.abort()
  })

  const hasRowActions = computed(() =>
    props.actions?.length > 0
    && props.actions?.filter((action: TableAction) => hasPermission(action)).length > 0,
  )
  const hasBulkActions = computed(() =>
    props.bulkActions
    && props.bulkActions?.length > 0
    && props.bulkActions?.filter((action: TableAction) => hasPermission(action)).length > 0,
  )

  onBeforeMount(() => {
    if (
      hasRowActions.value
      && !headers.value.some(header => header.value === 'action')
    ) {
      headers.value.push({ title: t('admin::admin.table.actions'), value: 'action' })
    }
  })

  function refresh () {
    deselectAll()
    loadData({
      page: page.value,
      perPage: perPage.value,
      sortBy: sortBy.value,
      search: search.value,
      filterValues: filtersData.value,
    })
  }

  function applyFilters (values: Record<string, any>) {
    filtersData.value = values
    loadData({
      page: 1,
      perPage: perPage.value,
      sortBy: sortBy.value,
      search: search.value,
      filterValues: filtersData.value,
    })
  }

  defineExpose({ refresh, defaultFilters: finalDefaultFilters.value })

  function deselectAll () {
    selected.value = []
  }

  function reloadFilters () {
    withFilters.value = true
  }

  const defaultCellComponents = [
    { key: 'is_active', component: TableActiveStatus },
    { key: 'agent', component: TableAgent },
    { key: 'user', component: TableUserInfo },
    { key: 'status', component: TableStatus },
    { key: 'type', component: TableEnum },
    { key: 'total', component: TableMoney },
    { key: 'amount', component: TableMoney },
    { key: 'thumbnail', component: TableThumbnail },
    { key: 'icon', component: TableThumbnail },
  ]
</script>

<template>
  <TableHeaderActions
    :actions="[...headerActions,...apiHeaderActions]"
    :item-id="itemId"
    :left-actions="leftHeaderActions"
    :module="module"
    :name="name"
    :refresh="refresh"
    :resource="resource"
  />
  <VCard>
    <TableHeader
      v-model="search"
      :bulk-actions="bulkActions"
      :deselect-all="deselectAll"
      :disabled="loading"
      :filters="filters"
      :has-search="hasSearch"
      :item-id="itemId"
      :loading="loading"
      :module="module"
      :name="name"
      :padding-top="paddingTop"
      :refresh="refresh"
      :resource="resource"
      :selected="selected"
      :title="title"
      @apply:filters="applyFilters"
      @reload:filters="reloadFilters"
    />
    <VDataTableServer
      v-model="selected"
      class="custom-table"
      :headers="headers.filter(h => !h.hidden)"
      hide-default-footer
      :item-value="itemId"
      :items="items"
      :items-length="pagination.total"
      :items-per-page="pagination.per_page"
      :loading="loading"
      :page="page"
      :show-select="hasBulkActions"
      @click:row="onRowClick"
      @update:sort-by="handleSortByUpdate"
    >
      <template #no-data>
        <div class="d-flex flex-column align-center justify-center py-6">
          <div class="text-caption text-medium-emphasis">
            {{
              t(search ? 'admin::admin.table.no_matching_records_found' : 'admin::admin.table.no_data_available')
            }}
          </div>
        </div>
      </template>

      <template
        v-for="(slotConfig, key) in cellComponents"
        :key="key"
        #[`item.${key}`]="{ item }"
      >
        <component
          :is="slotConfig.component"
          :item="item"
          v-bind="slotConfig.props||{}"
        />
      </template>
      <template
        v-for="slotConfig in defaultCellComponents"
        :key="slotConfig.key"
        #[`item.${slotConfig.key}`]="{ item }"
      >
        <component
          :is="slotConfig.component"
          :column="slotConfig.key"
          :item="item"
        />
      </template>

      <template v-if="actions?.length>0" #item.action="{ item }">
        <TableRowActions
          :actions="actions"
          :item="item"
          :item-id="itemId"
          :module="module"
          :name="name"
          :refresh="refresh"
          :resource="resource"
        />
      </template>

    </VDataTableServer>
    <TablePagination
      v-if="hasPagination"
      v-model:page="page"
      :disabled="loading"
      :pagination="pagination"
      :per-page="perPage"
      @update:per-page="perPage = $event"
    />
  </VCard>
</template>

<style lang="scss" scoped>
::v-deep(.v-data-table thead) {
  background-color: rgb(var(--v-table-head-bg));
  color: rgb(var(--v-on-table-head-bg));
}

::v-deep(.v-data-table thead th) {
  border-top: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
}

::v-deep(.v-data-table ) {
  border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-right: 0;
}

</style>
