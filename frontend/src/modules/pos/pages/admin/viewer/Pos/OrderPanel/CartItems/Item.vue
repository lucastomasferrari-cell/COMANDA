<script lang="ts" setup>
  import type { CartItem, CartItemAction, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import BtnProductVoid from './BtnProductVoid.vue'
  import EditQuantityDialog from './EditQuantityDialog.vue'
  import VoidItemDialog from './VoidItemDialog.vue'

  const props = defineProps<{
    cartItem: CartItem
    form: PosForm
    cart: UseCart
  }>()

  const { updateItem, processing, deleteItem, storeAction, removeAction } = props.cart
  const { t } = useI18n()
  const toast = useToast()
  const { edit: refetchOrder } = useOrder()

  const loading = ref<boolean>(false)
  const loadingDelete = ref<boolean>(false)
  const loadingDeleteAction = ref<boolean>(false)
  const loadingAction = ref<boolean>(false)
  const open = ref<boolean>(false)
  const editQtyDialog = ref<boolean>(false)
  const voidDialog = ref<boolean>(false)

  // orderProductId existe solo en edit mode (item persistido).
  const orderProductId = computed(() => props.cartItem.orderProduct?.id ?? null)
  const orderId = computed(() => props.cart.data.value.order?.id ?? null)
  const canVoidPersisted = computed(() =>
    props.form.mode === 'edit' && !!orderId.value && !!orderProductId.value,
  )
  const kitchenFired = computed(() =>
    !!props.cartItem.orderProduct?.status
    && props.cartItem.orderProduct.status.id !== 'pending',
  )

  // Estado de cocina del item: el cart devuelve cartItem.orderProduct.status
  // en modo edit. En modo create no hay status → item pendiente de envio.
  const kitchenStatusId = computed(() => props.cartItem.orderProduct?.status?.id ?? null)
  const kitchenStatusMeta = computed(() => {
    switch (kitchenStatusId.value) {
      case 'pending':
        return { icon: 'tabler-circle-dashed', color: 'warning', key: 'pending' }
      case 'preparing':
        return { icon: 'tabler-flame', color: 'warning', key: 'preparing' }
      case 'ready':
        return { icon: 'tabler-bell-ringing', color: 'info', key: 'ready' }
      case 'served':
        return { icon: 'tabler-check', color: 'success', key: 'served' }
      case 'cancelled':
        return { icon: 'tabler-x', color: 'error', key: 'cancelled' }
      case 'refunded':
        return { icon: 'tabler-arrow-back-up', color: 'error', key: 'refunded' }
      default:
        return { icon: 'tabler-circle-dot', color: 'grey', key: 'draft' }
    }
  })
  const kitchenStatusLabel = computed(() =>
    t(`pos::pos_viewer.item_menu.kitchen_status.${kitchenStatusMeta.value.key}`),
  )

  const hasLoyaltyGift = computed(() => props.cartItem?.loyaltyGift)
  const enableEditOrRemoveItem = computed(() => !props.cartItem.orderProduct?.status || props.cartItem.orderProduct?.status?.id === 'pending')
  const allowCancelProduct = computed(() => !hasLoyaltyGift.value && props.cartItem.orderProduct?.status?.id === 'preparing' && getActionNumberOfQuantity('cancel') < props.cartItem.qty)
  const allowRefundProduct = computed(() => !hasLoyaltyGift.value && props.cartItem.orderProduct && ['ready', 'served'].includes(props.cartItem.orderProduct?.status?.id || '') && getActionNumberOfQuantity('refund') < props.cartItem.qty)
  const isDisabled = computed(() => props.cartItem.orderProduct && ['cancelled', 'refunded'].includes(props.cartItem.orderProduct?.status?.id || ''))
  const disabledRemoveProduct = computed(() => props.form.mode == 'edit' && hasLoyaltyGift.value != null)

  const disabledIncreaseButton = computed(() => hasLoyaltyGift.value != null || !enableEditOrRemoveItem.value || loading.value)
  const disabledDecreaseButton = computed(() => hasLoyaltyGift.value != null || !enableEditOrRemoveItem.value || props.cartItem.qty == 1 || loading.value)

  const toggle = () => open.value = !open.value

  async function updateQty (qty: number) {
    if (qty < 1 || !enableEditOrRemoveItem.value || processing.value) {
      return
    }

    loading.value = true
    await updateItem(props.cartItem.id, qty)
    loading.value = false
  }

  async function removeItem () {
    // En edit mode con item persistido → void real con motivo + audit.
    // En create mode → delete del cart en memoria como vendor behavior.
    if (canVoidPersisted.value) {
      voidDialog.value = true
      return
    }
    if (processing.value) return
    loadingDelete.value = true
    await deleteItem(props.cartItem.id)
    loadingDelete.value = false
  }

  async function onVoided () {
    if (!orderId.value) return
    // Refrescar la orden completa para que el cart muestre totales
    // actualizados y el item voided deje de aparecer.
    try {
      const res = await refetchOrder(props.cart.cartId, orderId.value)
      // El emit init-order burbuja hacia arriba via OrderPanel → Pos/Index
      // → viewer/Index.vue. Emisión indirecta: escribimos el response
      // en cart.data (vendor ya lo hace en edit()) no hace falta.
      // Simplemente toast + auto-close del dialog es suficiente.
      props.cart.resetCart(res.data.body.cart)
    } catch {
      // fallback silencioso
    }
  }

  function getActionNumberOfQuantity (action: string): number {
    const actionObject = props.cartItem.actions?.find(
      (actionObject: CartItemAction) => actionObject.id === action,
    )
    return actionObject?.quantity ?? 0
  }

  async function refundProduct (quantity: number) {
    loadingAction.value = true
    await storeAction(props.cartItem.id, 'refund', quantity)
    loadingAction.value = false
  }

  async function cancelProduct (quantity: number) {
    loadingAction.value = true
    await storeAction(props.cartItem.id, 'cancel', quantity)
    loadingAction.value = false
  }

  function getActionLabel (action: string) {
    switch (action) {
      case 'cancel': {
        return t('admin::resource.cancel', { resource: t('product::products.product') })
      }
      case 'refund': {
        return t('admin::resource.refund', { resource: t('product::products.product') })
      }
      default: {
        return action
      }
    }
  }

  async function deleteAction (id: string) {
    if (processing.value) return
    loadingDeleteAction.value = true
    await removeAction(props.cartItem.id, id)
    loadingDeleteAction.value = false
  }

  function onConfirmEditQty (qty: number) {
    updateQty(qty)
  }

  function showPlaceholder (what: string) {
    toast.info(t(`pos::pos_viewer.item_menu.placeholders.${what}`))
  }
</script>

<template>
  <VCard
    class="cart-item"
    :disabled="isDisabled"
    :ripple="false"
    @click="toggle"
  >
    <VCardText class="pa-3 pt-1 pb-1">
      <div class="cart-item-content">
        <span class="cart-item-name ga-1">
          <VIcon v-if="cartItem.loyaltyGift" color="error" icon="tabler-gift" />
          <VTooltip :text="kitchenStatusLabel" location="top">
            <template #activator="{ props: tooltipProps }">
              <VIcon
                v-bind="tooltipProps"
                class="status-indicator"
                :color="kitchenStatusMeta.color"
                :icon="kitchenStatusMeta.icon"
                size="16"
              />
            </template>
          </VTooltip>
          {{ cartItem.item.name }}
        </span>
        <VTooltip :text="t('pos::pos_viewer.price')">
          <template #activator="{ props:tooltipProps }">
            <span
              class="cart-item-price"
              v-bind="tooltipProps"
            >
              {{ cartItem.unitPrice.formatted }}
            </span>
          </template>
        </VTooltip>

        <div class="qty-box d-flex align-center me-4">
          <v-btn
            :disabled="disabledDecreaseButton"
            icon="tabler-minus"
            size="x-small"
            variant="text"
            @click.stop.prevent="updateQty(cartItem.qty - 1)"
          />
          <span v-if="!loading" class="mx-2 text-subtitle-2">{{ cartItem.qty }}</span>
          <VProgressCircular
            v-else
            color="primary"
            indeterminate
            size="16"
            width="1.8"
          />
          <v-btn
            :disabled="disabledIncreaseButton"
            icon="tabler-plus"
            size="x-small"
            variant="text"
            @click.stop.prevent="updateQty(cartItem.qty + 1)"
          />
        </div>
        <VTooltip v-if="cartItem.taxTotal.amount>0" :text="t('pos::pos_viewer.total_taxes')">
          <template #activator="{ props:tooltipProps }">
            <span class="cart-item-price" v-bind="tooltipProps">
              {{ cartItem.taxTotal.formatted }}
            </span>
          </template>
        </VTooltip>
        <VTooltip :text="t('pos::pos_viewer.total')">
          <template #activator="{ props:tooltipProps }">
            <span class="cart-item-price cart-item-total" v-bind="tooltipProps">
              {{ cartItem.total.formatted }}
            </span>
          </template>
        </VTooltip>
        <VTooltip :text="canVoidPersisted
          ? t('pos::pos_viewer.void_item_action')
          : t('pos::pos_viewer.remove_item')"
        >
          <template #activator="{ props:tooltipProps }">
            <VBtn
              color="error"
              :disabled="disabledRemoveProduct || !enableEditOrRemoveItem || loadingDelete"
              :icon="canVoidPersisted ? 'tabler-circle-x' : 'tabler-trash'"
              :loading="loadingDelete"
              v-bind="tooltipProps"
              variant="text"
              @click.stop.prevent="removeItem"
            />
          </template>
        </VTooltip>

        <!-- Menu contextual con mas acciones. Las opciones con backend
             pendiente (nota, descuento por item, duplicar) muestran toast
             "Próximamente" — se habilitan cuando los endpoints existan. -->
        <VMenu location="bottom end" offset="4">
          <template #activator="{ props: menuProps }">
            <VBtn
              v-bind="menuProps"
              icon="tabler-dots-vertical"
              size="small"
              variant="text"
              @click.stop.prevent
            />
          </template>
          <VList density="compact">
            <VListItem
              :disabled="!enableEditOrRemoveItem"
              @click.stop="editQtyDialog = true"
            >
              <template #prepend><VIcon icon="tabler-edit" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.item_menu.edit_qty') }}
              </VListItemTitle>
            </VListItem>
            <VListItem @click.stop="showPlaceholder('note')">
              <template #prepend><VIcon icon="tabler-notes" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.item_menu.add_note') }}
              </VListItemTitle>
              <template #append>
                <VChip color="grey" density="compact" size="x-small">
                  {{ t('pos::pos_viewer.item_menu.coming_soon') }}
                </VChip>
              </template>
            </VListItem>
            <VListItem @click.stop="showPlaceholder('discount')">
              <template #prepend><VIcon icon="tabler-discount" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.item_menu.apply_discount') }}
              </VListItemTitle>
              <template #append>
                <VChip color="grey" density="compact" size="x-small">
                  {{ t('pos::pos_viewer.item_menu.coming_soon') }}
                </VChip>
              </template>
            </VListItem>
            <VListItem @click.stop="showPlaceholder('duplicate')">
              <template #prepend><VIcon icon="tabler-copy" /></template>
              <VListItemTitle>
                {{ t('pos::pos_viewer.item_menu.duplicate') }}
              </VListItemTitle>
              <template #append>
                <VChip color="grey" density="compact" size="x-small">
                  {{ t('pos::pos_viewer.item_menu.coming_soon') }}
                </VChip>
              </template>
            </VListItem>
          </VList>
        </VMenu>

        <BtnProductVoid
          v-if="allowCancelProduct"
          action="cancel"
          :cart-item="cartItem"
          color="error"
          icon="tabler-x"
          :label="t('admin::resource.cancel', {resource: t('product::products.product')})"
          :loading="loadingAction"
          @on-submit="cancelProduct"
        />
        <BtnProductVoid
          v-if="allowRefundProduct"
          action="refund"
          :cart-item="cartItem"
          color="error"
          icon="tabler-arrow-back-up"
          :label="t('admin::resource.refund', {resource: t('product::products.product')})"
          :loading="loadingAction"
          @on-submit="refundProduct"
        />
      </div>
      <div v-if="cartItem.actions.length>0" class="item-actions d-flex flex-wrap">
        <div
          v-for="(act, idx) in cartItem.actions"
          :key="idx"
          class="action-tag d-inline-flex align-center pl-2"
        >
          <span class="text-caption me-">
            {{ getActionLabel(act.id) }} {{ act.quantity }}
          </span>
          <v-btn
            color="error"
            :disabled="loadingDeleteAction"
            icon="tabler-trash"
            :loading="loadingDeleteAction"
            size="small"
            variant="text"
            @click.stop.prevent="deleteAction(act.id)"
          />
        </div>
      </div>
      <div v-if="open" class="cart-item-details">
        <div v-for="opt in cartItem.options" :key="opt.id" class="detail-line">
          <strong>{{ opt.name }}:</strong> <span class="item-option-value">
            {{ opt.values.map(v => v.label).join(', ') }}
          </span>
        </div>
      </div>

    </VCardText>
  </VCard>
  <EditQuantityDialog
    v-model="editQtyDialog"
    :initial="cartItem.qty"
    @confirm="onConfirmEditQty"
  />
  <VoidItemDialog
    v-if="canVoidPersisted"
    v-model="voidDialog"
    :item-name="cartItem.item.name"
    :kitchen-fired="kitchenFired"
    :order-id="orderId"
    :order-product-id="orderProductId"
    @voided="onVoided"
  />
</template>

<style lang="scss" scoped>
.cart-item {
  margin-bottom: 0.5rem;
}

.cart-item-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
}

.qty-box {
  border: 1px solid rgba(var(--v-border-color), 0.2);
  border-radius: 20px;
  padding: 2px 6px;
  margin: 10px 0;
  justify-content: space-between;
  min-width: 90px;
}

.qty-value {
  padding: 4px 10px;
  background: #f3f4f6;
  border-radius: 8px;
  font-weight: 600;
}

.cart-item-name {
  display: flex;
  align-items: center;
  font-weight: 700;
  width: 40%;
}

.status-indicator {
  flex-shrink: 0;
  margin-right: 0.25rem;
}

.cart-item-price {
  font-weight: 600;
  width: 10%;
}

.cart-item-total {
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
}

.detail-line strong {
  font-weight: 600;
  font-size: 0.7rem;
}

.item-option-value {
  font-size: 0.68rem;
  color: rgb(var(--v-theme-primary));
}

.item-actions {
  .action-tag {
    background-color: rgba(var(--v-theme-error), 0.06);
    border: 1px solid rgba(var(--v-theme-error), 0.3);
    border-radius: 8px;
    font-weight: 500;
  }
}
</style>
