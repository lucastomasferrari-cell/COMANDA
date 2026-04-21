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
  <div class="cart-wrapper mt-3">
    <template v-if="rows.length>0">
      <Item
        v-for="(cartItem) in rows"
        :key="cartItem.id"
        :cart="cart"
        :cart-item="cartItem as unknown as CartItem"
        :form="form"
      />
    </template>
    <div v-else class="empty-cart-wrapper">
      <VIcon icon="tabler-bowl-chopsticks" size="80" />
      <p class="empty-cart-title">
        {{ t('pos::pos_viewer.no_cart_items.title') }}
      </p>
      <p>
        {{ t('pos::pos_viewer.no_cart_items.description') }}
      </p>
    </div>
  </div>
</template>

<style lang="scss" scoped>
/* WRAPPER */
.cart-wrapper {
  height: 70dvh;
  background: rgba(var(--v-theme-grey-300), 0.7);
  overflow: auto;
  border: 1px dashed rgb(var(--v-theme-grey-200));
  border-radius: 15px;
  padding: 0.4rem;
}

.empty-cart-wrapper {
  height: 70dvh;
  display: flex;
  justify-content: center;
  flex-direction: column;
  align-items: center;
  font-size: 1rem;

  .empty-cart-title {
    margin: 0;
    font-weight: bold;
  }
}
</style>
