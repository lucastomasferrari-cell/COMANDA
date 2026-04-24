<script lang="ts" setup>
  import type { CartItem, UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm } from '@/modules/pos/contracts/posViewer.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import Item from './Item.vue'

  const props = defineProps<{
    cart: UseCart
    form: PosForm
  }>()
  const { data } = props.cart
  const { t } = useI18n()

  const rows = computed(() =>
    (data.value.items || []).map(item => {
      const optionsArray = item?.options ? Object.values(item.options as Record<string, any>) : []
      return {
        ...(item || {}),
        options: optionsArray.map(opt => ({
          ...(opt as Record<string, any>),
          values: (opt as Record<string, any>).values ?? [],
        })),
      }
    }),
  )
</script>

<template>
  <!-- Sprint 3.A fix — wrapper sin height fija 70dvh, sin background gris
       con border dashed. El scroll lo maneja el parent .check-panel__items
       con overflow-y:auto; acá solo pintamos los items en flujo natural.
       Empty state centered vertical/horizontal en el espacio disponible. -->
  <div class="cart-items-container">
    <template v-if="rows.length > 0">
      <Item
        v-for="cartItem in rows"
        :key="cartItem.id"
        :cart="cart"
        :cart-item="cartItem as unknown as CartItem"
        :form="form"
      />
    </template>
    <div v-else class="cart-empty">
      <VIcon
        class="cart-empty__icon mb-3"
        color="on-surface-variant"
        icon="tabler-shopping-cart"
        size="48"
      />
      <p class="cart-empty__text text-body-2">
        {{ t('pos::pos_viewer.no_cart_items.description') }}
      </p>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.cart-items-container {
  display: flex;
  flex-direction: column;
  min-height: 100%; /* ocupa todo el espacio del .check-panel__items padre
                       para que el empty state quede centrado */
}

/* Empty state Toast-style — flex center en el espacio disponible, sin
   ocupar ancho completo con bordes chillones. El ícono y el texto son
   on-surface-variant (bajo énfasis) — el cajero ya ve la estructura del
   panel, no hace falta gritarle "falta algo". */
.cart-empty {
  flex: 1 1 auto;
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
  text-align: center;
  color: rgb(var(--v-theme-on-surface-variant));
}

.cart-empty__text {
  max-width: 240px;
  margin: 0;
  color: rgb(var(--v-theme-on-surface-variant));
}
</style>
