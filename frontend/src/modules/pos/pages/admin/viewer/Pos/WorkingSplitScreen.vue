<script lang="ts" setup>
  import type { Cart, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { computed, ref } from 'vue'
  import { useDisplay } from 'vuetify'
  import ActiveOrdersPanel from './ActiveOrdersPanel/Index.vue'
  import MenuPanel from './MenuPanel/Index.vue'
  import OrderPanel from './OrderPanel/Index.vue'

  // Sprint 4 commit 2 — extracción del split-screen "working" (3 cols:
  // ActiveOrders colapsado + grid productos + check) a componente
  // propio. Este componente lo renderiza CADA modo (Salón / Mostrador /
  // Pedidos) cuando hay orden activa en ese modo. Caja no lo usa
  // (es full-screen).
  //
  // Por qué inside-de-modo en vez de fuera (como Sprint 3.A.bis):
  //   Con KeepAlive en los modos, cada modo cachea su sub-estado. Si el
  //   working vivía afuera, abrir orden en Salón → ir a Mostrador →
  //   volver a Salón perdía la orden (working se desmontaba al cambiar
  //   modo). Embebido adentro, el cache de KeepAlive del modo preserva
  //   home Y working según corresponda.

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

  const display = useDisplay()
  const isNarrow = computed(() => !display.lgAndUp.value)

  // Ref local del MenuPanel para que ESC desde OrderPanel pueda
  // delegarle el foco al search interno. Antes vivia en Pos/Index.vue;
  // mas correcto que viva acá donde MenuPanel y OrderPanel son hermanos.
  const menuPanelRef = ref<any>(null)
  const onFocusMenuSearch = (): void => {
    menuPanelRef.value?.focusSearch?.()
  }

  defineExpose({
    focusSearch: onFocusMenuSearch,
  })
</script>

<template>
  <div
    class="pos-layout pos-layout--working flex-grow-1"
    :class="{ 'pos-layout--narrow': isNarrow }"
  >
    <aside v-if="!isNarrow" class="pos-panel pos-panel--orders">
      <VCard class="pos-col-card">
        <ActiveOrdersPanel
          :active-order-id="meta.order?.id ?? null"
          :branch-id="form.branchId"
          :cart-id="cart.cartId"
          :collapsed="hasActiveOrder"
          @init-order="(response: Record<string, any>) => emit('init-order', response)"
          @new-order="emit('new-order')"
        />
      </VCard>
    </aside>
    <main class="pos-panel pos-panel--main">
      <VCard class="pos-col-card">
        <VCardText class="pa-3">
          <MenuPanel
            ref="menuPanelRef"
            :cart="cart"
            :form="form"
            :has-active-order="hasActiveOrder"
            :meta="meta"
            @init-order="(response: Record<string, any>) => emit('init-order', response)"
          />
        </VCardText>
      </VCard>
    </main>
    <aside class="pos-panel pos-panel--cart">
      <VCard class="pos-col-card">
        <VCardText class="pa-3">
          <OrderPanel
            :cart="cart"
            :form="form"
            :has-active-order="hasActiveOrder"
            :meta="meta"
            :qintrix="qintrix"
            @focus-menu-search="onFocusMenuSearch"
            @on-click-action="(action: string) => emit('on-click-action', action)"
            @reset="(cartData?: Cart) => emit('reset', cartData)"
            @store-payment="(orderId: number | string) => emit('store-payment', orderId)"
          />
        </VCardText>
      </VCard>
    </aside>
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

.pos-layout--working {
  grid-template-columns: 22% 48% 30%;
}

.pos-layout--narrow {
  grid-template-columns: 1fr;
}

.pos-layout--narrow.pos-layout--working {
  grid-template-columns: 1fr;
  grid-auto-rows: minmax(0, 1fr);
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
