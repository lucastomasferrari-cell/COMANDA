<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { Product } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'

  const emit = defineEmits(['open-options-dialog'])

  const props = defineProps<{
    product: Product
    cart: UseCart
    categoryColorMap?: Map<number | string, string>
  }>()

  const { t } = useI18n()
  const { data, storeItem, showError, processing } = props.cart

  const loading = ref<boolean>(false)
  const hasOptions = computed(() => (props.product.options?.length ?? 0) > 0)
  const hasDiscount = computed(() => (props.product.selling_price?.amount ?? 0) < (props.product.price?.amount ?? 0))

  // Color de la categoria primaria del producto (primer category_id que
  // este en el mapa). Fallback a gris neutro.
  const categoryColor = computed(() => {
    if (!props.categoryColorMap) return '#B0B0B0'
    for (const id of props.product.category_ids ?? []) {
      const c = props.categoryColorMap.get(id)
      if (c) return c
    }
    return '#B0B0B0'
  })

  const addProductToCart = async () => {
    if (processing.value) {
      return
    }
    if (hasOptions.value) {
      emit('open-options-dialog', props.product)
    } else {
      try {
        loading.value = true
        processing.value = true
        const response = await storeItem({
          product_id: props.product.id,
          options: {},
          qty: 1,
        })
        data.value = response.data.body
      } catch (error: any) {
        showError(error)
      } finally {
        loading.value = false
        processing.value = false
      }
    }
  }
</script>

<template>
  <VCard
    class="product-item mb-3 me-2"
    :ripple="false"
    :style="{ '--category-color': categoryColor }"
    @click="addProductToCart"
  >
    <VCardText class="pa-3">
      <div class="image-wrapper">
        <VImg
          v-if="product.thumbnail"
          class="product-image"
          cover
          height="100"
          rounded="lg"
          :src="product.thumbnail"
        />
        <VIcon v-else class="icon" icon="tabler-soup" size="100" />
        <div v-if="product.is_new" class="new-badge">
          {{ t('pos::pos_viewer.new').toUpperCase() }}
        </div>
      </div>
      <div class="product-name mt-3">{{ product.name }}</div>
      <div class="product-price mt-1">
        <span v-if="hasDiscount" class="selling-price">
          {{ product.selling_price?.formatted }}
        </span>
        <span :class="{ 'original-price': hasDiscount }">
          {{ product.price?.formatted }}
        </span>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.product-item {
  cursor: pointer;
  border: 2px dashed #ededed;
  border-radius: 0.2rem;
  /* Borde superior 4px con el color de la categoria primaria. */
  border-top: 4px solid var(--category-color, #B0B0B0);

  .product-name {
    font-weight: 600;
    font-size: 0.8rem;
    margin-bottom: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .product-price {
    display: flex;
    gap: 0.5rem;
    align-items: center;

    .original-price {
      text-decoration: line-through;
      color: gray;
      font-size: 0.79rem;
    }

    .selling-price {
      color: #e53935;
      font-weight: 600;
    }
  }

  .new-badge {
    position: absolute;
    top: 10px;
    right: 10px;
  }

  .image-wrapper {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;

    .icon {
      color: rgb(var(--v-theme-grey-500));
    }

    .new-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: rgb(var(--v-theme-error));
      color: white;
      padding: 4px 8px;
      font-size: 0.75rem;
      font-weight: bold;
      border-radius: 4px;
    }
  }
}
</style>
