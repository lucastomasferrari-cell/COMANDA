<script lang="ts" setup>
  import type { PlanoTable } from '@/modules/seatingPlan/components/SalonPlanoVisual.vue'
  import type { Cart, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { computed } from 'vue'
  import { useDisplay } from 'vuetify'
  import ActiveOrdersPanel from './ActiveOrdersPanel/Index.vue'
  import MenuPanel from './MenuPanel/Index.vue'
  import PausedOrderBanner from './PausedOrderBanner.vue'
  import WorkingSplitScreen from './WorkingSplitScreen.vue'

  // Sprint 4 commit 2 — SalonModeView ahora renderiza el sub-estado
  // según hasActiveOrder:
  //   - true  → WorkingSplitScreen (3 cols productos+check, igual que
  //             Mostrador/Pedidos cuando entran a una orden).
  //   - false → home Salón (ActiveOrders + plano de mesas).
  // Con KeepAlive en el padre (commit 3), cambiar de modo NO desmonta
  // este componente; volver a Salón restituye el sub-estado vigente.

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
    (e: 'pick-table-free', table: PlanoTable): void
    (e: 'pick-table-occupied', table: PlanoTable): void
    (e: 'tables-count', count: number): void
  }>()

  const display = useDisplay()
  const isNarrow = computed(() => !display.lgAndUp.value)
</script>

<template>
  <!-- (1) Working: en orden activa, split-screen 3 cols compartido. -->
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

  <!-- (2) Home Salón: ActiveOrders + plano + banner orden pausada. -->
  <div
    v-else
    class="salon-home flex-grow-1 d-flex flex-column"
  >
    <PausedOrderBanner
      :cart-id="cart.cartId"
      @init-order="(response: Record<string, any>) => emit('init-order', response)"
    />
    <div
      class="pos-layout flex-grow-1"
      :class="{ 'pos-layout--narrow': isNarrow }"
    >
      <aside v-if="!isNarrow" class="pos-panel pos-panel--orders">
        <VCard class="pos-col-card">
          <ActiveOrdersPanel
            :active-order-id="meta.order?.id ?? null"
            :branch-id="form.branchId"
            :cart-id="cart.cartId"
            :collapsed="false"
            @init-order="(response: Record<string, any>) => emit('init-order', response)"
            @new-order="emit('new-order')"
          />
        </VCard>
      </aside>
      <main class="pos-panel pos-panel--main">
        <VCard class="pos-col-card">
          <VCardText class="pa-3">
            <MenuPanel
              :cart="cart"
              :form="form"
              :has-active-order="false"
              :meta="meta"
              @init-order="(response: Record<string, any>) => emit('init-order', response)"
              @pick-table-free="(table: PlanoTable) => emit('pick-table-free', table)"
              @pick-table-occupied="(table: PlanoTable) => emit('pick-table-occupied', table)"
              @tables-count="(count: number) => emit('tables-count', count)"
            />
          </VCardText>
        </VCard>
      </main>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.pos-layout {
  display: grid;
  grid-template-columns: 30% 70%;
  gap: 8px;
  overflow: hidden;
  min-height: 0;
}

.pos-layout--narrow {
  grid-template-columns: 1fr;
}

.pos-panel {
  min-width: 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.pos-col-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.pos-col-card > :deep(.v-card-text) {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}
</style>
