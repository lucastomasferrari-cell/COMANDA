<script lang="ts" setup>
  import type { Cart, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { useI18n } from 'vue-i18n'
  import WorkingSplitScreen from './WorkingSplitScreen.vue'

  // Sprint 3.A.bis bug 2 — Placeholder del modo Mostrador (home).
  // Sprint 4 commit 2 — además del placeholder, ahora MostradorModeView
  // delega al WorkingSplitScreen cuando hay orden activa, igual que
  // Salón. Esto permite que cada modo tenga su propio sub-estado
  // home/working consistente con KeepAlive.

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

  // Sprint 3.A.bis: el click "+ Nueva" emitía sin nextTick y
  // crasheaba __vnode al desmontar el placeholder. Sprint 4 commit 2:
  // ya no es problema porque KeepAlive impide el unmount, pero el
  // ripple=false + emit directo siguen siendo el patrón correcto
  // (no agrega valor el ripple en un CTA grande).
  const onClickNew = (): void => {
    emit('new-order')
  }
</script>

<template>
  <!-- Working: si entrás a una orden desde Mostrador (placeholder no
       genera órdenes, pero la "+ Nueva" del banner futuro sí), se
       renderiza el split-screen igual que Salón. -->
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

  <!-- Home Mostrador: placeholder hasta Sprint 3.C. -->
  <div v-else class="mostrador-placeholder">
    <aside class="mostrador-placeholder__side">
      <div class="mostrador-placeholder__side-title">
        {{ t('pos::pos_viewer.modes_placeholders.mostrador.side_title') }}
      </div>
      <div class="mostrador-placeholder__side-empty">
        <VIcon class="mb-2" color="grey-500" icon="tabler-receipt" size="32" />
        <p class="text-body-2 text-medium-emphasis mb-0">
          {{ t('pos::pos_viewer.modes_placeholders.mostrador.side_empty') }}
        </p>
      </div>
    </aside>
    <main class="mostrador-placeholder__main">
      <div class="icon-wrapper mb-4">
        <VIcon color="primary" icon="tabler-bolt" size="48" />
      </div>
      <h3 class="text-h6 font-weight-bold mb-2">
        {{ t('pos::pos_viewer.modes_placeholders.mostrador.title') }}
      </h3>
      <p class="text-body-2 text-medium-emphasis text-center mb-6" style="max-width: 420px;">
        {{ t('pos::pos_viewer.modes_placeholders.mostrador.description') }}
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
    </main>
  </div>
</template>

<style lang="scss" scoped>
.mostrador-placeholder {
  display: grid;
  grid-template-columns: 30% 70%;
  gap: 8px;
  height: 100%;
  min-height: 0;
  overflow: hidden;
}

.mostrador-placeholder__side {
  display: flex;
  flex-direction: column;
  padding: 16px;
  background: rgb(var(--v-theme-surface));
  border-radius: 8px;
  min-height: 0;
}

.mostrador-placeholder__side-title {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: rgba(var(--v-theme-on-surface), 0.6);
  margin-bottom: 12px;
}

.mostrador-placeholder__side-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px 16px;
  color: rgba(var(--v-theme-on-surface), 0.5);
}

.mostrador-placeholder__main {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgb(var(--v-theme-surface));
  border-radius: 8px;
  padding: 32px;
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
