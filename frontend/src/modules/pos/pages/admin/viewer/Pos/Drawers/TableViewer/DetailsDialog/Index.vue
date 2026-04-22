<script lang="ts" setup>
import type { UseCart } from '@/modules/cart/composables/cart.ts'
import type { PosForm } from '@/modules/pos/contracts/posViewer.ts'
import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
import { computed, onBeforeMount, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { useAuth } from '@/modules/auth/composables/auth.ts'
import { useForm } from '@/modules/core/composables/form.ts'
import { useTable } from '@/modules/seatingPlan/composables/table.ts'
import GuestCountDialog from '../../../Dialogs/GuestCountDialog.vue'
import MergeDetails from './MergeDetails.vue'
import MergeDialog from './MergeDialog.vue'
import OrdersDetails from './OrdersDetails/Index.vue'

const props = defineProps<{
  modelValue: boolean
  tableId: number
  form: PosForm
  cart: UseCart
  qintrix: ReturnType<typeof useQintrix>
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'close-parent' | 'refresh'): void
  (e: 'store-payment' | 'refund-order' | 'cancel-order' | 'view-order' | 'print-order', value: string | number): void
  (e: 'init-order', response: Record<string, any>): void
}>()

const currentTableId = ref(props.tableId)
const { t } = useI18n()
const toast = useToast()
const { can } = useAuth()
const { getTableViewerDetails, assignWaiter, updateStatusToAvailable, loadings, splitTable } = useTable()
const { storeOrderType } = props.cart
const { form } = toRefs(props)

const loadingCreateOrder = ref(false)
const showGuestCountDialog = ref(false)
const table = ref<Record<string, any> | null>(null)
const meta = reactive({
  waiters: [] as Record<string, any>[],
})
const loading = ref(true)
const showTableMergeDialog = ref(false)
const allowMergeTable = computed(() =>
  table.value
  && can('admin.tables.merge')
  && !table.value.current_merge
  && !['cleaning', 'merged'].includes(table.value.status.id),
)

const dialogModel = computed({
  get: () => props.modelValue,
  set: (val: boolean) => emit('update:modelValue', val),
})

onBeforeMount(async () => {
  loading.value = true
  await loadData()
  loading.value = false
})

const assignWaiterForm = useForm({
  waiter_id: null,
})

async function loadData() {
  try {
    const response = await getTableViewerDetails(currentTableId.value)
    const data = response.data.body as Record<string, any>
    table.value = data.table
    meta.waiters = data.meta?.waiters || []
    assignWaiterForm.state.waiter_id = table.value?.waiter?.id ?? null
  } catch {
    toast.error(t('core::errors.an_unexpected_error_occurred'))
  }
}

if (can('admin.tables.assign_waiter')) {
  watch(
    () =>
      assignWaiterForm.state.waiter_id,
    async (newValue, oldValue) => {
      if (!table.value) return
      if (newValue == table.value.waiter?.id) return
      if (await assignWaiterForm.submit(() => assignWaiter(currentTableId.value, { waiter_id: newValue }))) {
        if (!table.value.waiter) {
          table.value.waiter = {}
        }
        table.value.waiter.id = newValue
      } else {
        assignWaiterForm.state.waiter_id = oldValue
      }
    },
  )
}

const close = () => emit('update:modelValue', false)

function createOrder() {
  // Antes disparaba storeOrderType + form.table directamente; ahora primero
  // pedimos comensales. Si el user cancela, nada se toca.
  if (!table.value) return
  showGuestCountDialog.value = true
}

async function confirmGuestCount(guestCount: number) {
  if (!table.value) return
  loadingCreateOrder.value = true
  try {
    await storeOrderType('dine_in')
    form.value.table = {
      id: table.value.id,
      name: table.value.name,
      floor: table.value.floor.name,
      zone: table.value.zone.name,
    }
    form.value.meta.guestCount = guestCount
  } finally {
    loadingCreateOrder.value = false
  }
  emit('close-parent')
  close()
}

function initOrder(response: Record<string, any>) {
  emit('init-order', response)
  emit('close-parent')
  close()
}

async function makeAvailable() {
  await updateStatusToAvailable(currentTableId.value)
  emit('refresh')
  await loadData()
}

async function refreshAll() {
  emit('refresh')
  await loadData()
}

function mergeTable() {
  showTableMergeDialog.value = true
}

async function onClickSplitTable() {
  if (table.value?.allow_split && await splitTable(table.value?.current_merge.id)) {
    emit('refresh')
    await loadData()
  }
}

async function reload(newTableId: number) {
  if (newTableId != currentTableId.value) {
    currentTableId.value = newTableId
    loading.value = true
    await loadData()
    loading.value = false
  }
}

defineExpose({ refresh: refreshAll })

</script>

<template>
  <VDialog v-model="dialogModel" max-width="700" scrollable>
    <VCard>
      <div v-if="loading" class="d-flex justify-center align-center" style="height: 250px;">
        <VProgressCircular color="primary" indeterminate size="50" />
      </div>
      <template v-else-if="table">
        <VCardTitle class="d-flex justify-space-between align-center  font-weight-bold text-h6">
          <div class="d-flex align-center gap-2">
            <VIcon icon="tabler-brand-airtable" />
            {{ table?.name }}
          </div>
          <VChip :color="table?.status.color" label size="small">
            {{ table?.status.name }}
          </VChip>
        </VCardTitle>

        <VCardText class="pt-1">
          <VRow dense>
            <VCol cols="4">
              <div class="text-caption text-grey">
                {{ t('seatingplan::tables.table_columns.capacity') }}
              </div>
              <div class="font-weight-medium">{{ table.capacity }}</div>
            </VCol>
            <VCol cols="4">
              <div class="text-caption text-grey">
                {{ t('seatingplan::tables.table_columns.floor') }}
              </div>
              <div class="font-weight-medium">{{ table.floor.name }}</div>
            </VCol>
            <VCol cols="4">
              <div class="text-caption text-grey">
                {{ t('seatingplan::tables.table_columns.zone') }}
              </div>
              <div class="font-weight-medium">{{ table.zone.name }}</div>
            </VCol>

            <VCol cols="12" md="12">
              <div class="text-caption text-grey">
                {{ t('seatingplan::attributes.tables.waiter_id') }}
              </div>
              <VSelect v-if="can('admin.tables.assign_waiter')" v-model="assignWaiterForm.state.waiter_id" class="mt-2"
                clearable :disabled="assignWaiterForm.loading.value" :error="!!assignWaiterForm.errors.value?.waiter_id"
                :error-messages="assignWaiterForm.errors.value?.waiter_id" item-title="name" item-value="id"
                :items="meta.waiters" :loading="assignWaiterForm.loading.value"
                :placeholder="t('seatingplan::attributes.tables.waiter_id')" />
              <div v-else class="font-weight-medium">
                {{ table.waiter?.name || t('seatingplan::tables.unassigned') }}
              </div>
            </VCol>
            <VCol v-if="table.current_merge" cols="12">
              <MergeDetails :current-table-id="currentTableId" :merge="table.current_merge" @reload="reload" />
            </VCol>
            <VCol v-if="table.order || table.orders" cols="12">
              <OrdersDetails :branch-id="form.branchId" :cart-id="cart.cartId" :table="table"
                :qintrix="qintrix"
                @cancel-order="(orderId: number | string) => $emit('cancel-order', orderId)" @init-order="initOrder"
                @print-order="(orderId: number | string) => $emit('print-order', orderId)" @refresh="refreshAll"
                @refund-order="(orderId: number | string) => $emit('refund-order', orderId)"
                @store-payment="(orderId: number | string) => $emit('store-payment', orderId)"
                @view-order="(orderId: number | string) => $emit('view-order', orderId)" />
            </VCol>
            <VCol class="mt-3" cols="12">
              <div class="d-flex justify-end gap-2">
                <VBtn v-if="allowMergeTable" color="default" variant="tonal" @click="mergeTable">
                  <VIcon color="info" icon="tabler-arrow-merge" start />
                  {{ t('seatingplan::tables.merge_table') }}
                </VBtn>
                <VBtn v-if="table.allow_split" color="default" :disabled="loadings.split" :loading="loadings.split"
                  variant="tonal" @click="onClickSplitTable">
                  <VIcon color="success" icon="tabler-arrows-diagonal" start />
                  {{ t('seatingplan::tables.split_table') }}
                </VBtn>
                <VBtn v-if="can('admin.orders.create') && table.status.id === 'available'" color="default"
                  :disabled="loadingCreateOrder" :loading="loadingCreateOrder" variant="tonal" @click="createOrder">
                  <VIcon color="primary" icon="tabler-plus" start />
                  {{ t('seatingplan::tables.create_order') }}
                </VBtn>
                <VBtn v-if="can('admin.tables.update_status') && table.status.id == 'cleaning'" color="default"
                  :disabled="loadings.makeAvailable" :loading="loadings.makeAvailable" variant="tonal"
                  @click="makeAvailable">
                  <VIcon color="success" icon="tabler-progress-check" start />
                  {{ t('seatingplan::tables.make_available') }}
                </VBtn>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </template>
    </VCard>
    <MergeDialog v-if="allowMergeTable && showTableMergeDialog" v-model="showTableMergeDialog" :pos-form="form"
      :table-id="currentTableId" @refresh="refreshAll" />
    <GuestCountDialog
      v-model="showGuestCountDialog"
      :initial="form.meta.guestCount ?? 1"
      @confirm="confirmGuestCount"
    />
  </VDialog>
</template>

<style lang="scss" scoped></style>
