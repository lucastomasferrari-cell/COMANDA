<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, ref, toRefs } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import AddCustomerDialog from './AddCustomerDialog.vue'
  import DateTimePicker from '@/modules/core/components/Form/DateTimePicker.vue'

  const props = defineProps<{
    cart: UseCart
    form: PosForm
    meta: PosMeta
  }>()

  const emit = defineEmits<{
    (e: 'back-to-map'): void
    (e: 'action', key: string): void
  }>()

  const { t } = useI18n()
  const { can, hasRole } = useAuth()

  const { processing, data, addCustomer, removeCustomer, storeOrderType } = props.cart
  const { form } = toRefs(props)

  // Título contextual Toast-style.
  //   dine_in → "Mesa N"
  //   takeout/pick_up → "Retiro - Nombre" (sin customer: "Retiro")
  //   drive_thru → "Drive-thru"
  //   pre_order/catering → "Pedido anticipado"/"Catering"
  //   counter (default sin table/type especial) → "Mostrador"
  const contextualTitle = computed<string>(() => {
    if (props.form.table != null) {
      return props.form.table.name ?? t('pos::pos_viewer.check_header.context.table_fallback')
    }
    const orderTypeId = data.value.orderType?.id
    const customer = data.value.customer
    const customerSuffix = customer?.name ? ` — ${customer.name}` : ''
    switch (orderTypeId) {
      case 'takeaway':
      case 'pick_up':
        return t('pos::pos_viewer.check_header.context.takeout') + customerSuffix
      case 'drive_thru':
        return t('pos::pos_viewer.check_header.context.drive_thru') + customerSuffix
      case 'pre_order':
        return t('pos::pos_viewer.check_header.context.pre_order') + customerSuffix
      case 'catering':
        return t('pos::pos_viewer.check_header.context.catering') + customerSuffix
      default:
        return t('pos::pos_viewer.check_header.context.counter') + customerSuffix
    }
  })

  // Chip comensales: solo dine_in, editable con popover +/-. Para
  // otros canales (takeout/delivery) el cliente y sus datos viven en
  // el título contextual y en su dialog propio.
  const showGuestPopover = ref(false)
  const updateGuestCount = (delta: number): void => {
    const next = Math.max(1, Number(props.form.meta.guestCount ?? 1) + delta)
    form.value.meta.guestCount = next
  }

  // Overflow menu — 4 dialogs inline + 3 emits al parent para acciones
  // que ya existían (hold_order, more_print, cancel_order) + 2 stubs
  // placeholder (split_bill, transfer_table).
  const showChangeTypeDialog = ref(false)
  const showAssignWaiterDialog = ref(false)
  const showAssignCustomerDialog = ref(false)
  const showAddCustomerDialog = ref(false)
  const showAdditionalFieldsDialog = ref(false)

  // Selección temporal de los dialogs antes de aplicar:
  const pendingOrderType = ref<string | null>(null)
  const pendingWaiterId = ref<number | null>(null)
  const pendingCustomerId = ref<number | null>(null)

  const nonDineInTypes = computed(() =>
    (props.meta.orderTypes ?? []).filter((type: any) => type.id !== 'dine_in'))

  const needsAdditionalFields = computed(() => {
    const id = data.value.orderType?.id
    return id === 'drive_thru' || id === 'pre_order' || id === 'catering'
  })

  // Handlers que cierran el dialog y aplican el cambio.
  const applyOrderType = async (): Promise<void> => {
    if (!pendingOrderType.value || processing.value) return
    await storeOrderType(pendingOrderType.value)
    showChangeTypeDialog.value = false
  }

  const applyWaiter = (): void => {
    const w = (props.meta.waiters ?? []).find((u: any) => u.id === pendingWaiterId.value)
    if (w) form.value.waiter = w
    showAssignWaiterDialog.value = false
  }

  const applyCustomer = async (): Promise<void> => {
    const c = (props.meta.customers ?? []).find((u: any) => u.id === pendingCustomerId.value)
    if (!c) return
    data.value.customer = c
    await addCustomer(c.id)
    showAssignCustomerDialog.value = false
  }

  const clearCustomer = async (): Promise<void> => {
    data.value.customer = null
    await removeCustomer()
    showAssignCustomerDialog.value = false
  }

  const onCustomerCreated = (customer: Record<string, any>): void => {
    data.value.customer = customer
    showAddCustomerDialog.value = false
  }

  // Open handlers — los setean al valor actual para que el dialog arranque
  // con el estado vigente y no en blanco.
  const openChangeType = (): void => {
    pendingOrderType.value = data.value.orderType?.id ?? null
    showChangeTypeDialog.value = true
  }

  const openAssignWaiter = (): void => {
    pendingWaiterId.value = form.value.waiter?.id ?? null
    showAssignWaiterDialog.value = true
  }

  const openAssignCustomer = (): void => {
    pendingCustomerId.value = data.value.customer?.id ?? null
    showAssignCustomerDialog.value = true
  }

  // Delegación al OrderPanel parent.
  const emitAction = (key: string): void => { emit('action', key) }
</script>

<template>
  <div class="check-header">
    <!-- Fila 1: back + título + overflow -->
    <div class="check-header__top d-flex align-center ga-2">
      <VBtn
        class="check-header__back"
        prepend-icon="tabler-arrow-left"
        size="small"
        variant="text"
        @click="$emit('back-to-map')"
      >
        {{ t('pos::pos_viewer.back_to_map') }}
      </VBtn>

      <h2 class="check-header__title text-h6 font-weight-medium flex-grow-1">
        {{ contextualTitle }}
      </h2>

      <VMenu location="bottom end" offset="4">
        <template #activator="{ props: menuProps }">
          <VBtn
            v-bind="menuProps"
            aria-label="Más opciones"
            class="check-header__overflow"
            icon="tabler-dots-vertical"
            size="default"
            variant="text"
          />
        </template>
        <VList density="compact" min-width="240">
          <!-- Contexto / asignación -->
          <VListItem v-if="!form.table" @click="openChangeType">
            <template #prepend><VIcon icon="tabler-arrows-exchange" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.change_type') }}</VListItemTitle>
          </VListItem>
          <VListItem v-if="!hasRole('waiter')" @click="openAssignWaiter">
            <template #prepend><VIcon icon="tabler-user-pentagon" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.assign_waiter') }}</VListItemTitle>
          </VListItem>
          <VListItem @click="openAssignCustomer">
            <template #prepend><VIcon icon="tabler-users" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.assign_customer') }}</VListItemTitle>
          </VListItem>
          <VListItem v-if="needsAdditionalFields" @click="showAdditionalFieldsDialog = true">
            <template #prepend><VIcon icon="tabler-forms" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.additional_fields') }}</VListItemTitle>
          </VListItem>

          <VDivider class="my-1" />

          <!-- Transacción — aplicar descuento/cupón se conectan en 3.A.10.
               Hasta entonces disparan emit('action',key) que el parent
               gatilla al bloque Discount todavía visible en el footer. -->
          <VListItem @click="emitAction('more_discount')">
            <template #prepend><VIcon icon="tabler-discount" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.apply_discount') }}</VListItemTitle>
          </VListItem>
          <VListItem @click="emitAction('more_voucher')">
            <template #prepend><VIcon icon="tabler-ticket" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.apply_voucher') }}</VListItemTitle>
          </VListItem>
          <VListItem disabled @click="emitAction('more_split_bill')">
            <template #prepend><VIcon icon="tabler-columns-2" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.split_bill') }}</VListItemTitle>
            <template #append>
              <VChip color="grey" density="compact" size="x-small">
                {{ t('pos::pos_viewer.more_actions.coming_soon') }}
              </VChip>
            </template>
          </VListItem>

          <VDivider class="my-1" />

          <!-- Gestión -->
          <VListItem
            v-if="form.table"
            disabled
            @click="emitAction('more_change_table')"
          >
            <template #prepend><VIcon icon="tabler-arrows-right-left" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.transfer_table') }}</VListItemTitle>
            <template #append>
              <VChip color="grey" density="compact" size="x-small">
                {{ t('pos::pos_viewer.more_actions.coming_soon') }}
              </VChip>
            </template>
          </VListItem>
          <VListItem @click="emitAction('hold_order')">
            <template #prepend><VIcon icon="tabler-player-pause" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.hold_order') }}</VListItemTitle>
          </VListItem>
          <VListItem
            :disabled="!can('admin.orders.print')"
            @click="emitAction('more_print')"
          >
            <template #prepend><VIcon icon="tabler-printer" /></template>
            <VListItemTitle>{{ t('pos::pos_viewer.check_header.overflow.print_prebill') }}</VListItemTitle>
          </VListItem>

          <VDivider class="my-1" />

          <!-- Destructivo — rojo. Requiere PIN según FILOSOFIA §7
               (validación vive en el handler del parent OrderPanel). -->
          <VListItem @click="emitAction('cancel_order')">
            <template #prepend><VIcon color="error" icon="tabler-trash" /></template>
            <VListItemTitle class="text-error">
              {{ t('pos::pos_viewer.check_header.overflow.cancel_order') }}
            </VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
    </div>

    <!-- Fila 2: chips de contexto. Por ahora solo guest_count para dine_in
         es editable inline (caso principal AR). Customer phone/address en
         takeout/delivery se muestran en el título contextual; edit via
         overflow "Asignar cliente". -->
    <div v-if="form.table" class="check-header__chips d-flex flex-wrap ga-2 mt-2">
      <VMenu
        v-model="showGuestPopover"
        :close-on-content-click="false"
        location="bottom start"
        offset="6"
      >
        <template #activator="{ props: chipProps }">
          <VChip
            v-bind="chipProps"
            class="guest-chip"
            color="primary"
            prepend-icon="tabler-users"
            size="large"
            variant="tonal"
          >
            {{ form.meta.guestCount ?? 1 }}
          </VChip>
        </template>
        <VCard class="pa-3" min-width="180">
          <div class="text-caption mb-2">
            {{ t('order::attributes.orders.guest_count') }}
          </div>
          <div class="d-flex align-center justify-center ga-2">
            <VBtn
              aria-label="decrement"
              :disabled="Number(form.meta.guestCount ?? 1) <= 1"
              icon="tabler-minus"
              size="default"
              variant="tonal"
              @click="updateGuestCount(-1)"
            />
            <span class="text-h5 mx-3" style="min-width: 40px; text-align: center;">
              {{ form.meta.guestCount ?? 1 }}
            </span>
            <VBtn
              aria-label="increment"
              icon="tabler-plus"
              size="default"
              variant="tonal"
              @click="updateGuestCount(1)"
            />
          </div>
        </VCard>
      </VMenu>
    </div>

    <!-- Dialog: Cambiar tipo de orden -->
    <VDialog v-model="showChangeTypeDialog" max-width="380">
      <VCard>
        <VCardTitle>{{ t('pos::pos_viewer.check_header.overflow.change_type') }}</VCardTitle>
        <VCardText>
          <VRadioGroup v-model="pendingOrderType" hide-details>
            <VRadio
              v-for="type in nonDineInTypes"
              :key="type.id"
              :label="type.name"
              :value="type.id"
            />
          </VRadioGroup>
        </VCardText>
        <VCardActions class="px-5 pb-5 ga-2">
          <VSpacer />
          <VBtn variant="text" @click="showChangeTypeDialog = false">
            {{ t('admin::admin.buttons.cancel') }}
          </VBtn>
          <VBtn color="primary" variant="flat" @click="applyOrderType">
            {{ t('admin::admin.buttons.confirm') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Dialog: Asignar mozo -->
    <VDialog v-model="showAssignWaiterDialog" max-width="420">
      <VCard>
        <VCardTitle>{{ t('pos::pos_viewer.check_header.overflow.assign_waiter') }}</VCardTitle>
        <VCardText>
          <VAutocomplete
            v-model="pendingWaiterId"
            autofocus
            item-title="name"
            item-value="id"
            :items="meta.waiters ?? []"
            :menu-props="{ maxHeight: 250 }"
            :placeholder="t('pos::pos_viewer.select_waiter')"
            prepend-inner-icon="tabler-user-pentagon"
            clearable
          />
        </VCardText>
        <VCardActions class="px-5 pb-5 ga-2">
          <VSpacer />
          <VBtn variant="text" @click="showAssignWaiterDialog = false">
            {{ t('admin::admin.buttons.cancel') }}
          </VBtn>
          <VBtn color="primary" variant="flat" @click="applyWaiter">
            {{ t('admin::admin.buttons.confirm') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Dialog: Asignar cliente -->
    <VDialog v-model="showAssignCustomerDialog" max-width="460">
      <VCard>
        <VCardTitle class="d-flex align-center">
          <span class="flex-grow-1">{{ t('pos::pos_viewer.check_header.overflow.assign_customer') }}</span>
          <VBtn
            v-if="can('admin.customers.create')"
            color="primary"
            prepend-icon="tabler-user-plus"
            size="small"
            variant="tonal"
            @click="showAddCustomerDialog = true"
          >
            {{ t('pos::pos_viewer.check_header.new_customer') }}
          </VBtn>
        </VCardTitle>
        <VCardText>
          <VAutocomplete
            v-model="pendingCustomerId"
            autofocus
            item-title="name"
            item-value="id"
            :items="meta.customers ?? []"
            :menu-props="{ maxHeight: 250 }"
            :placeholder="t('pos::pos_viewer.select_customer')"
            prepend-inner-icon="tabler-users"
            clearable
          />
        </VCardText>
        <VCardActions class="px-5 pb-5 ga-2">
          <VBtn
            v-if="data.customer"
            color="error"
            variant="text"
            @click="clearCustomer"
          >
            {{ t('pos::pos_viewer.check_header.remove_customer') }}
          </VBtn>
          <VSpacer />
          <VBtn variant="text" @click="showAssignCustomerDialog = false">
            {{ t('admin::admin.buttons.cancel') }}
          </VBtn>
          <VBtn color="primary" variant="flat" @click="applyCustomer">
            {{ t('admin::admin.buttons.confirm') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Dialog: Datos adicionales (scheduled_at + car_plate según dining type). -->
    <VDialog v-model="showAdditionalFieldsDialog" max-width="420">
      <VCard>
        <VCardTitle>{{ t('pos::pos_viewer.check_header.overflow.additional_fields') }}</VCardTitle>
        <VCardText>
          <VRow dense>
            <VCol
              v-if="['pre_order', 'catering'].includes(data.orderType?.id as string)"
              cols="12"
            >
              <DateTimePicker
                v-model="form.meta.scheduledAt"
                clearable
                :label="t('order::attributes.orders.scheduled_at')"
                :min="new Date().toLocaleDateString('en-CA')"
              />
            </VCol>
            <template v-if="data.orderType?.id === 'drive_thru'">
              <VCol cols="12">
                <VTextField
                  v-model="form.meta.carPlate"
                  clearable
                  :label="t('order::attributes.orders.car_plate')"
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="form.meta.carDescription"
                  clearable
                  :label="t('order::attributes.orders.car_description')"
                />
              </VCol>
            </template>
          </VRow>
        </VCardText>
        <VCardActions class="px-5 pb-5 ga-2">
          <VSpacer />
          <VBtn color="primary" variant="flat" @click="showAdditionalFieldsDialog = false">
            {{ t('admin::admin.buttons.close') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- AddCustomerDialog vendor-existente — se dispara desde Asignar cliente. -->
    <AddCustomerDialog
      v-if="can('admin.customers.create') && showAddCustomerDialog"
      v-model="showAddCustomerDialog"
      @created="onCustomerCreated"
    />
  </div>
</template>

<style lang="scss" scoped>
/* Header Toast-style. Botón back chico text (no compite con el título),
   título text-h6 peso 500 (no bold bold, peso medio para legibilidad
   cómoda), overflow ⋮ a la derecha. Chips debajo en una fila flex-wrap
   por si se acumulan varios (caso delivery con dirección + canal). */
.check-header {
  padding: 8px 4px 4px 4px;
}

.check-header__top {
  min-height: 40px;
}

.check-header__title {
  /* Trunca si el título es muy largo (ej "Catering — Cliente muy largo"). */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin: 0;
}

.check-header__overflow {
  flex-shrink: 0;
}

.guest-chip {
  font-weight: 600;
  letter-spacing: 0.02em;
}
</style>
