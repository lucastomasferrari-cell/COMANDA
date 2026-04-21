<script lang="ts" setup>
  import type { CartItem, CartItemAction, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm } from '@/modules/pos/contracts/posViewer.ts'
  import { ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import BtnProductVoid from './BtnProductVoid.vue'

  const props = defineProps<{
    cartItem: CartItem
    form: PosForm
    cart: UseCart
  }>()

  const { updateItem, processing, deleteItem, storeAction, removeAction } = props.cart
  const { t } = useI18n()

  const loading = ref<boolean>(false)
  const loadingDelete = ref<boolean>(false)
  const loadingDeleteAction = ref<boolean>(false)
  const loadingAction = ref<boolean>(false)
  const open = ref<boolean>(false)

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
    if (processing.value) return
    loadingDelete.value = true
    await deleteItem(props.cartItem.id)
    loadingDelete.value = false
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
        <VTooltip :text="t('pos::pos_viewer.remove_item')">
          <template #activator="{ props:tooltipProps }">
            <VBtn
              color="error"
              :disabled="disabledRemoveProduct || !enableEditOrRemoveItem || loadingDelete"
              icon="tabler-trash"
              :loading="loadingDelete"
              v-bind="tooltipProps"
              variant="text"
              @click.stop.prevent="removeItem"
            />
          </template>
        </VTooltip>
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
