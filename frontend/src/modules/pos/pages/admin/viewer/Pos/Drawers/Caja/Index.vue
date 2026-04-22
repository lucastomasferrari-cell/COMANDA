<script lang="ts" setup>
  import { storeToRefs } from 'pinia'
  import { computed, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import type { PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { usePosCashMovement } from '@/modules/pos/composables/posCashMovement.ts'
  import { usePosSession } from '@/modules/pos/composables/posSession.ts'

  /**
   * Caja drawer del POS (reemplaza Movimiento de efectivo).
   * Muestra estado de la sesión actual, totales del turno, registra
   * ingresos/egresos y cierra caja con arqueo físico.
   *
   * Cerrar caja: el arqueo físico aquí es MVP — valida que cash_actual
   * sea numérico y persiste la diferencia. El Bloque 12 anti-fraude
   * agrega umbral de diferencia, motivo obligatorio y manager passcode.
   */
  const props = defineProps<{
    modelValue: boolean
    registerId: number | null
    sessionId: number | null
    meta: PosMeta
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'session-closed'): void
  }>()

  const { t } = useI18n()
  const toast = useToast()
  const appStore = useAppStore()
  const { isRtl } = storeToRefs(appStore)
  const drawerTransition = computed(() =>
    isRtl.value ? 'slide-x-transition' : 'slide-x-reverse-transition',
  )

  const open = computed({
    get: () => props.modelValue,
    set: val => emit('update:modelValue', val),
  })

  const { store: storeMovement, index: listMovements } = usePosCashMovement()
  const { getShowData: showSession, close: closeSession } = usePosSession()

  const session = ref<Record<string, any> | null>(null)
  const movements = ref<Record<string, any>[]>([])
  const loading = ref(false)
  const closeCajaDialog = ref(false)

  async function refresh () {
    if (!props.sessionId) return
    loading.value = true
    try {
      const [sRes, mRes] = await Promise.all([
        showSession(props.sessionId),
        listMovements({ pos_session_id: props.sessionId, per_page: 50 }),
      ])
      session.value = sRes.data ?? null
      movements.value = mRes.data.body?.items ?? mRes.data.body ?? []
    } catch {
      toast.error(t('core::errors.an_unexpected_error_occurred'))
    } finally {
      loading.value = false
    }
  }

  watch(() => props.modelValue, val => { if (val) refresh() })

  // Totales por dirección. El backend envía amount como objeto Money.
  const totalIn = computed(() =>
    movements.value
      .filter(m => m.direction === 'in' || m.direction?.id === 'in')
      .reduce((sum, m) => sum + Number(m.amount?.amount ?? m.amount ?? 0), 0),
  )
  const totalOut = computed(() =>
    movements.value
      .filter(m => m.direction === 'out' || m.direction?.id === 'out')
      .reduce((sum, m) => sum + Number(m.amount?.amount ?? m.amount ?? 0), 0),
  )
  const openingBalance = computed(() =>
    Number(session.value?.opening_balance?.amount ?? session.value?.opening_balance ?? 0),
  )
  const expectedCash = computed(() => openingBalance.value + totalIn.value - totalOut.value)

  // Form de nuevo movimiento — mismo que el CashMovement original.
  const movementForm = useForm({
    pos_register_id: props.registerId,
    reference: null,
    amount: null,
    notes: null,
    reason: null,
    direction: 'out',
  })

  const filteredReasons = computed(() => props.meta.reasons[movementForm.state.direction] || [])
  watch(() => movementForm.state.direction, () => { movementForm.state.reason = null })

  const isSubmitDisabled = computed(() =>
    !movementForm.state.direction
    || !movementForm.state.reason
    || !movementForm.state.amount
    || Number.parseFloat(movementForm.state.amount) < 1,
  )

  async function submitMovement () {
    if (isSubmitDisabled.value || movementForm.loading.value) return
    const ok = await movementForm.submit(() => storeMovement(movementForm.state))
    if (ok) {
      movementForm.state.reference = null
      movementForm.state.amount = null
      movementForm.state.notes = null
      movementForm.state.reason = null
      movementForm.state.direction = 'out'
      await refresh()
      toast.success(t('pos::pos_viewer.caja.movement_saved'))
    }
  }

  // Cerrar caja — MVP. Bloque 12 endurece con umbrales + passcode.
  const closeForm = useForm({
    cash_actual: '',
    notes: '',
  })
  const closeDifference = computed(() => {
    const actual = Number.parseFloat(closeForm.state.cash_actual || '0')
    return actual - expectedCash.value
  })
  const closeIsDisabled = computed(() =>
    closeForm.state.cash_actual === '' || isNaN(Number(closeForm.state.cash_actual)),
  )

  function openCloseDialog () {
    closeForm.state.cash_actual = ''
    closeForm.state.notes = ''
    closeCajaDialog.value = true
  }

  async function confirmCloseSession () {
    if (closeIsDisabled.value || !props.sessionId || closeForm.loading.value) return
    const ok = await closeForm.submit(() => closeSession(props.sessionId!, {
      cash_actual: Number(closeForm.state.cash_actual),
      notes: closeForm.state.notes,
      expected_cash: expectedCash.value,
      difference: closeDifference.value,
    }))
    if (ok) {
      toast.success(t('pos::pos_viewer.caja.closed_ok'))
      closeCajaDialog.value = false
      emit('session-closed')
      open.value = false
    }
  }

  function formatMoney (amount: number | null | undefined): string {
    const n = Number(amount ?? 0)
    return `${props.meta.currency ?? ''} ${n.toFixed(2)}`
  }
</script>

<template>
  <VNavigationDrawer
    v-model="open"
    color="grey-light"
    :location="isRtl?'left':'right'"
    temporary
    :transition="drawerTransition"
    :width="640"
  >
    <VCard class="h-100 d-flex flex-column" color="grey-light">
      <VCardText class="pa-4 flex-grow-1 overflow-y-auto">
        <div class="d-flex mb-3 justify-space-between align-center">
          <h3 class="d-flex align-center ga-2">
            <VIcon color="#16a085" icon="tabler-cash" size="26" />
            {{ t('pos::pos_viewer.caja.title') }}
          </h3>
          <VBtn
            color="grey-200"
            icon="tabler-x"
            @click="open = false"
          />
        </div>

        <!-- Summary sesión -->
        <VCard v-if="sessionId" class="mb-3 pa-3" variant="tonal">
          <div class="d-flex justify-space-between align-center mb-2">
            <div class="text-subtitle-2">
              {{ t('pos::pos_viewer.caja.session_active') }}
              <span class="text-caption text-medium-emphasis ms-1">#{{ sessionId }}</span>
            </div>
            <VBtn
              :disabled="loading"
              :loading="loading"
              icon="tabler-refresh"
              size="small"
              variant="text"
              @click="refresh"
            />
          </div>
          <VRow dense>
            <VCol cols="6">
              <div class="text-caption text-medium-emphasis">
                {{ t('pos::pos_viewer.caja.opening_balance') }}
              </div>
              <div class="font-weight-medium">{{ formatMoney(openingBalance) }}</div>
            </VCol>
            <VCol cols="6">
              <div class="text-caption text-medium-emphasis">
                {{ t('pos::pos_viewer.caja.expected_cash') }}
              </div>
              <div class="font-weight-bold text-primary">{{ formatMoney(expectedCash) }}</div>
            </VCol>
            <VCol cols="6">
              <div class="text-caption text-medium-emphasis">
                {{ t('pos::pos_viewer.caja.total_in') }}
              </div>
              <div class="text-success font-weight-medium">+ {{ formatMoney(totalIn) }}</div>
            </VCol>
            <VCol cols="6">
              <div class="text-caption text-medium-emphasis">
                {{ t('pos::pos_viewer.caja.total_out') }}
              </div>
              <div class="text-error font-weight-medium">- {{ formatMoney(totalOut) }}</div>
            </VCol>
          </VRow>
        </VCard>

        <div v-else class="text-center py-6 text-medium-emphasis">
          <VIcon class="mb-2" icon="tabler-alert-circle" size="32" />
          <div>{{ t('pos::pos_viewer.caja.no_session') }}</div>
        </div>

        <!-- Nuevo movimiento -->
        <div v-if="sessionId" class="mb-4">
          <div class="text-subtitle-2 mb-2">
            {{ t('pos::pos_viewer.caja.new_movement') }}
          </div>
          <VForm @submit.prevent="submitMovement">
            <div class="directions mb-2">
              <div
                v-for="direction in meta.directions"
                :key="direction.id"
                class="direction-card"
                :class="{ active: movementForm.state.direction === direction.id }"
                @click="movementForm.state.direction = direction.id"
              >
                <VIcon :color="direction.color" :icon="direction.icon" />
                <span class="name">{{ direction.name }}</span>
              </div>
            </div>
            <VChipGroup
              v-model="movementForm.state.reason"
              base-color="default"
              class="mb-2"
              column
              mandatory
            >
              <VChip
                v-for="reason in filteredReasons"
                :key="reason.id"
                color="primary"
                size="small"
                :value="reason.id"
              >
                <VIcon v-if="reason.icon" :icon="reason.icon" start />
                {{ reason.name }}
              </VChip>
            </VChipGroup>
            <VRow dense>
              <VCol cols="6">
                <VTextField
                  v-model="movementForm.state.amount"
                  v-decimal-en
                  density="compact"
                  :error="!!movementForm.errors.value?.amount"
                  :error-messages="movementForm.errors.value?.amount"
                  :label="t('pos::attributes.pos_cash_movements.amount')"
                >
                  <template #prepend-inner>
                    {{ meta.currency }}
                  </template>
                </VTextField>
              </VCol>
              <VCol cols="6">
                <VTextField
                  v-model="movementForm.state.reference"
                  density="compact"
                  :label="t('pos::attributes.pos_cash_movements.reference')"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="movementForm.state.notes"
                  density="compact"
                  :label="t('pos::attributes.pos_cash_movements.notes')"
                  rows="2"
                />
              </VCol>
              <VCol cols="12">
                <VBtn
                  block
                  color="primary"
                  :disabled="isSubmitDisabled || movementForm.loading.value"
                  :loading="movementForm.loading.value"
                  size="large"
                  @click="submitMovement"
                >
                  <VIcon icon="tabler-plus" start />
                  {{ t('pos::pos_viewer.caja.register_movement') }}
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </div>

        <!-- Listado de movimientos -->
        <div v-if="sessionId" class="movements-list">
          <div class="d-flex justify-space-between align-center mb-2">
            <div class="text-subtitle-2">
              {{ t('pos::pos_viewer.caja.movements_title') }}
              <span v-if="movements.length" class="text-caption text-medium-emphasis ms-1">
                ({{ movements.length }})
              </span>
            </div>
          </div>
          <div v-if="movements.length === 0" class="text-center py-4 text-medium-emphasis text-caption">
            {{ t('pos::pos_viewer.caja.no_movements') }}
          </div>
          <VCard
            v-for="m in movements"
            :key="m.id"
            class="mb-1 pa-2"
            variant="outlined"
          >
            <div class="d-flex align-center ga-2">
              <VIcon
                :color="(m.direction?.id ?? m.direction) === 'in' ? 'success' : 'error'"
                :icon="(m.direction?.id ?? m.direction) === 'in' ? 'tabler-arrow-up-right' : 'tabler-arrow-down-left'"
                size="18"
              />
              <div class="flex-grow-1">
                <div class="text-body-2 font-weight-medium">
                  {{ m.reason?.name ?? m.reason ?? '—' }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ m.created_at }} · {{ m.creator?.name ?? m.created_by?.name ?? '—' }}
                </div>
              </div>
              <div
                class="font-weight-bold"
                :class="(m.direction?.id ?? m.direction) === 'in' ? 'text-success' : 'text-error'"
              >
                {{ (m.direction?.id ?? m.direction) === 'in' ? '+' : '-' }}
                {{ m.amount?.formatted ?? formatMoney(m.amount) }}
              </div>
            </div>
          </VCard>
        </div>
      </VCardText>

      <!-- Footer acción cerrar caja -->
      <div v-if="sessionId" class="caja-footer pa-3 border-top">
        <VBtn
          block
          color="error"
          prepend-icon="tabler-lock"
          size="large"
          variant="tonal"
          @click="openCloseDialog"
        >
          {{ t('pos::pos_viewer.caja.close_session') }}
        </VBtn>
      </div>
    </VCard>
  </VNavigationDrawer>

  <!-- Dialog cerrar caja con arqueo -->
  <VDialog v-model="closeCajaDialog" max-width="440" persistent>
    <VCard class="pa-2">
      <VCardText>
        <h3 class="text-h6 mb-2">{{ t('pos::pos_viewer.caja.close_title') }}</h3>
        <p class="text-caption text-medium-emphasis mb-4">
          {{ t('pos::pos_viewer.caja.close_subtitle') }}
        </p>
        <VRow dense>
          <VCol cols="6">
            <div class="text-caption text-medium-emphasis">
              {{ t('pos::pos_viewer.caja.expected_cash') }}
            </div>
            <div class="text-h6">{{ formatMoney(expectedCash) }}</div>
          </VCol>
          <VCol cols="6">
            <VTextField
              v-model="closeForm.state.cash_actual"
              v-decimal-en
              autofocus
              :error="!!closeForm.errors.value?.cash_actual"
              :error-messages="closeForm.errors.value?.cash_actual"
              :label="t('pos::pos_viewer.caja.cash_actual')"
              type="number"
            >
              <template #prepend-inner>
                {{ meta.currency }}
              </template>
            </VTextField>
          </VCol>
          <VCol cols="12">
            <div
              class="difference-box pa-2 mt-2 rounded"
              :class="closeDifference === 0 ? 'bg-success-lighten-4' : 'bg-warning-lighten-4'"
            >
              <div class="text-caption text-medium-emphasis">
                {{ t('pos::pos_viewer.caja.difference') }}
              </div>
              <div
                class="text-h6 font-weight-bold"
                :class="closeDifference < 0 ? 'text-error' : (closeDifference > 0 ? 'text-warning' : 'text-success')"
              >
                {{ closeDifference >= 0 ? '+' : '' }}{{ formatMoney(closeDifference) }}
              </div>
            </div>
          </VCol>
          <VCol v-if="closeDifference !== 0" cols="12">
            <VTextarea
              v-model="closeForm.state.notes"
              :label="t('pos::pos_viewer.caja.justification')"
              rows="2"
            />
          </VCol>
        </VRow>
        <div class="d-flex ga-2 mt-3">
          <VBtn block color="grey-500" variant="tonal" @click="closeCajaDialog = false">
            {{ t('admin::admin.buttons.cancel') }}
          </VBtn>
          <VBtn
            block
            color="error"
            :disabled="closeIsDisabled || closeForm.loading.value"
            :loading="closeForm.loading.value"
            @click="confirmCloseSession"
          >
            {{ t('pos::pos_viewer.caja.confirm_close') }}
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.directions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.direction-card {
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 8px;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.15s ease;

  .name {
    font-size: 0.85rem;
    font-weight: 600;
  }

  &.active {
    border-color: rgb(var(--v-theme-primary));
    background: rgba(var(--v-theme-primary), 0.06);
  }
}

.caja-footer {
  border-top: thin solid rgba(var(--v-theme-on-surface), 0.1);
  flex-shrink: 0;
}

.difference-box {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
}
</style>
