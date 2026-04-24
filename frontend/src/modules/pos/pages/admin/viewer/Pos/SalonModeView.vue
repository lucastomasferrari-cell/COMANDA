<script lang="ts" setup>
  import type { PlanoTable } from '@/modules/seatingPlan/components/SalonPlanoVisual.vue'
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed } from 'vue'
  import { useDisplay } from 'vuetify'
  import ActiveOrdersPanel from './ActiveOrdersPanel/Index.vue'
  import MenuPanel from './MenuPanel/Index.vue'

  // Sprint 4 commit 1 — extracción del "home Salón" a componente propio.
  // Refactor sin cambio funcional: el contenido del v-else-if='salon'
  // de Pos/Index.vue pasa acá tal cual. Sirve de base para el commit 2,
  // donde KeepAlive necesita un único hijo por slot del switcher de modo.
  //
  // Nota: este componente sólo cubre la "home" del Salón (mostrar
  // ActiveOrders + plano cuando no hay orden activa). El split-screen
  // working con check-panel sigue viviendo en Pos/Index.vue por ahora;
  // en commit 2 movemos también ese sub-estado adentro de los modos.

  const props = defineProps<{
    cart: UseCart
    form: PosForm
    meta: PosMeta
  }>()

  const emit = defineEmits<{
    (e: 'init-order', response: Record<string, any>): void
    (e: 'new-order'): void
    (e: 'pick-table-free', table: PlanoTable): void
    (e: 'pick-table-occupied', table: PlanoTable): void
    (e: 'tables-count', count: number): void
  }>()

  const display = useDisplay()
  const isNarrow = computed(() => !display.lgAndUp.value)
</script>

<template>
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
</template>

<style lang="scss" scoped>
/* Sprint 4 commit 1 — mismas reglas que Pos/Index.vue tenia para
   .pos-layout (vista home con 2 cols 30/70). Mantenemos --narrow para
   md-and-down, donde el ActiveOrdersPanel pasa a drawer en el padre. */
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
