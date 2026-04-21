<script lang="ts" setup>
import { storeToRefs } from 'pinia'
import type { UseCart } from '@/modules/cart/composables/cart.ts'
import type { PosForm } from '@/modules/pos/contracts/posViewer.ts'
import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
import { computed, reactive, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { useAuth } from '@/modules/auth/composables/auth.ts'
import { useAppStore } from '@/modules/core/stores/appStore.ts'
import { useTable } from '@/modules/seatingPlan/composables/table.ts'
import DetailsDialog from './DetailsDialog/Index.vue'
import Empty from './Empty.vue'
import Filters from './Filters.vue'
import Table from './Table.vue'

const props = defineProps<{
  modelValue: boolean
  branchId: number | null
  form: PosForm
  cart: UseCart
  qintrix: ReturnType<typeof useQintrix>
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'store-payment' | 'refund-order' | 'cancel-order' | 'view-order' | 'print-order', value: string | number): void
  (e: 'init-order', response: Record<string, any>): void
}>()

const open = computed({
  get: () => props.modelValue,
  set: val => emit('update:modelValue', val),
})

const { t } = useI18n()
const appStore = useAppStore()
const { isRtl } = storeToRefs(appStore)
const drawerTransition = computed(() => isRtl.value ? 'slide-x-transition' : 'slide-x-reverse-transition')
const toast = useToast()
const { getTableViewer } = useTable()
const { can } = useAuth()

const loading = ref(false)
const ordersDetailsRef = ref()

const tables = ref<any[]>([])
const tableDetailsDialog = reactive<{
  tableId: number | null
  show: boolean
}>({
  tableId: null,
  show: true,
})

const meta = reactive({
  statuses: [] as { id: string, name: string }[],
  floors: [] as { id: string, name: string }[],
  zones: [] as { id: string, name: string }[],
})

const filters = reactive({
  search: '',
  floors: [] as string[],
  zones: [] as string[],
  statuses: [] as string[],
})

const close = () => emit('update:modelValue', false)

watch(
  () => props.modelValue,
  async newVal => {
    if (newVal) await loadData({ resetFilters: true })
  },
)

function resetFilters() {
  filters.search = ''
  filters.floors = []
  filters.statuses = []
  filters.zones = []
}

async function loadData({ refreshing = false, resetFilters: shouldResetFilters = false }: { refreshing?: boolean, resetFilters?: boolean } = {}) {
  try {
    if (!refreshing) {
      loading.value = true
    }

    if (shouldResetFilters) {
      resetFilters()
    }

    const response = await getTableViewer(props.branchId, {
      search: filters.search,
      floors: filters.floors,
      zones: filters.zones,
      statuses: filters.statuses,
    })
    const data = response.data.body

    tables.value = data.tables || []
    const filtersData = data.filters || { statuses: [], floors: [], zones: [] }
    meta.statuses = filtersData.statuses || []
    meta.floors = filtersData.floors || []
    meta.zones = filtersData.zones || []
  } catch {
    toast.error(t('core::errors.an_unexpected_error_occurred'))
  } finally {
    loading.value = false
  }
}

function showTableDetails(tableId: number) {
  if (can('admin.tables.show')) {
    tableDetailsDialog.tableId = tableId
    tableDetailsDialog.show = true
  }
}

defineExpose({
  refreshTableDetails: () => {
    if (can('admin.tables.show') && tableDetailsDialog.show && tableDetailsDialog.tableId) {
      ordersDetailsRef.value?.refresh()
    }
  },
})

watchDebounced(
  () => ({
    search: filters.search,
    floors: [...filters.floors],
    zones: [...filters.zones],
    statuses: [...filters.statuses],
  }),
  async () => {
    if (props.modelValue) {
      await loadData({ refreshing: true })
    }
  },
  { debounce: 250, maxWait: 600 },
)

</script>

<template>
  <v-navigation-drawer v-model="open" color="grey-light" :location="isRtl ? 'left' : 'right'" temporary
    :transition="drawerTransition" :width="600">
    <VCard color="grey-light">
      <VCardText>
        <div class="d-flex mb-4 justify-space-between align-center">
          <h3 class="d-flex align-center ga-1">
            <VIcon color="#8e44ad" icon="tabler-brand-airtable" size="25" />
            {{ t('pos::pos_viewer.table_viewer') }}
          </h3>
          <VBtn color="grey-200" icon="tabler-x" @click="close" />
        </div>
        <Filters v-if="tables.length > 0" :disabled="loading" :filters="filters" :loading="loading" :meta="meta" />
        <div class="tables-wrapper mt-3">
          <VRow v-if="!loading && tables.length > 0" dense>
            <VCol v-for="table in tables" :key="table.id" cols="12" md="6">
              <Table :table="table" @show-details="showTableDetails" />
            </VCol>
          </VRow>
          <Empty v-else-if="!loading && tables.length === 0" />
          <div v-else-if="loading" class="tables-loading">
            <VProgressCircular color="primary" indeterminate size="50" />
          </div>
        </div>
      </VCardText>
    </VCard>
    <DetailsDialog v-if="can('admin.tables.show') && tableDetailsDialog.show && tableDetailsDialog.tableId"
      ref="ordersDetailsRef" v-model="tableDetailsDialog.show" :cart="cart" :form="form"
      :qintrix="qintrix"
      :table-id="tableDetailsDialog.tableId" @cancel-order="(orderId: number | string) => $emit('cancel-order', orderId)"
      @close-parent="close" @init-order="(response: Record<string, any>) => $emit('init-order', response)"
      @print-order="(orderId: number | string) => $emit('print-order', orderId)" @refresh="loadData({ refreshing: true })"
      @refund-order="(orderId: number | string) => $emit('refund-order', orderId)"
      @store-payment="(orderId: number | string) => $emit('store-payment', orderId)"
      @view-order="(orderId: number | string) => $emit('view-order', orderId)" />
  </v-navigation-drawer>
</template>

<style lang="scss" scoped>
.tables-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 50%;
}

.tables-wrapper {
  height: 83vh;
  overflow-y: auto;
  overflow-x: hidden;
}
</style>
