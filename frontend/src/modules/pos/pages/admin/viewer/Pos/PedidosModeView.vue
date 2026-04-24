<script lang="ts" setup>
  import type { Cart, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { useI18n } from 'vue-i18n'
  import PausedOrderBanner from './PausedOrderBanner.vue'
  import WorkingSplitScreen from './WorkingSplitScreen.vue'

  // Sprint 3.A.bis bug 2 — Placeholder del modo Pedidos (home).
  // Sprint 4 commit 2 — además del placeholder, delega al
  // WorkingSplitScreen cuando hay orden activa.

  const props = defineProps<{
    cart: UseCart
    form: PosForm
    meta: PosMeta
    qintrix: ReturnType<typeof useQintrix>
    hasActiveOrder: boolean
  }>()

  const emit = defineEmits<{
    (e: 'init-order', response: Record<string, any>): void
    (e: 'reset', cart?: Cart): void
    (e: 'new-order'): void
    (e: 'on-click-action', action: string): void
    (e: 'store-payment', orderId: number | string): void
  }>()

  const { t } = useI18n()

  const onClickNew = (): void => {
    emit('new-order')
  }
</script>

<template>
  <WorkingSplitScreen
    v-if="hasActiveOrder"
    :cart="cart"
    :form="form"
    :has-active-order="hasActiveOrder"
    :meta="meta"
    :qintrix="qintrix"
    @init-order="(response: Record<string, any>) => emit('init-order', response)"
    @new-order="emit('new-order')"
    @on-click-action="(action: string) => emit('on-click-action', action)"
    @reset="(cart?: Cart) => emit('reset', cart)"
    @store-payment="(orderId: number | string) => emit('store-payment', orderId)"
  />

  <div v-else class="pedidos-home d-flex flex-column flex-grow-1">
    <PausedOrderBanner
      :cart-id="cart.cartId"
      @init-order="(response: Record<string, any>) => emit('init-order', response)"
    />
    <div class="pedidos-placeholder flex-grow-1">
    <div class="icon-wrapper mb-4">
      <VIcon color="primary" icon="tabler-clipboard-list" size="48" />
    </div>
    <h3 class="text-h6 font-weight-bold mb-2">
      {{ t('pos::pos_viewer.modes_placeholders.pedidos.title') }}
    </h3>
    <p class="text-body-2 text-medium-emphasis text-center mb-6" style="max-width: 460px;">
      {{ t('pos::pos_viewer.modes_placeholders.pedidos.description') }}
    </p>
    <VBtn
      color="primary"
      prepend-icon="tabler-plus"
      :ripple="false"
      size="large"
      @click="onClickNew"
    >
      {{ t('pos::pos_viewer.modes_placeholders.cta_new') }}
    </VBtn>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.pedidos-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgb(var(--v-theme-surface));
  border-radius: 8px;
  padding: 48px 32px;
  height: 100%;
  min-height: 0;
}

.icon-wrapper {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(var(--v-theme-primary), 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
