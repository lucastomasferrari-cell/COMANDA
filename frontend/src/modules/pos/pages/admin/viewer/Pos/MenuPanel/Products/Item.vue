<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { Product } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'
  import ProductTileImage from './ProductTileImage.vue'

  const emit = defineEmits(['open-options-dialog'])

  const props = defineProps<{
    product: Product
    cart: UseCart
    categoryHueMap?: Map<number | string, number>
  }>()

  const { t } = useI18n()
  const { data, storeItem, showError, processing } = props.cart

  const loading = ref<boolean>(false)
  const hasOptions = computed(() => (props.product.options?.length ?? 0) > 0)
  const hasDiscount = computed(() => (props.product.selling_price?.amount ?? 0) < (props.product.price?.amount ?? 0))

  // Hue (0-360) de la categoria primaria. Para el placeholder y el borde
  // izquierdo si se activa el color por hue. Default null → ProductTileImage
  // usa coral marca (hue 12).
  // (categoryColor hex legacy eliminado — el backend ya no lo expone; el
  // tile usa el hue via has-hue class o cae al border token neutro.)
  const categoryHue = computed<number | null>(() => {
    if (!props.categoryHueMap) return null
    for (const id of props.product.category_ids ?? []) {
      const h = props.categoryHueMap.get(id)
      if (typeof h === 'number') return h
    }
    return null
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
  <!-- Sprint 1.B: borde izquierdo 4px con hsl(hue 55% 50%) cuando la
       categoría tiene color_hue. Sin hue → cae al border token neutro. -->
  <VCard
    class="product-item mb-3 me-2"
    :class="{ 'has-hue': categoryHue !== null }"
    :ripple="false"
    :style="{ '--category-hue': categoryHue ?? 12 }"
    @click="addProductToCart"
  >
    <!-- ProductTileImage reemplaza el VImg + VIcon tabler-soup genérico
         (anti-patrón: todos los productos mostraban la misma sopita).
         Ahora foto si existe o iniciales tinteadas por el hue de la categoría. -->
    <div class="image-wrapper">
      <ProductTileImage
        :category-color-hue="categoryHue"
        :name="product.name"
        :thumbnail="product.thumbnail"
      />
      <div v-if="product.is_new" class="new-badge">
        {{ t('pos::pos_viewer.new').toUpperCase() }}
      </div>
    </div>
    <VCardText class="pa-2">
      <div class="product-name">{{ product.name }}</div>
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
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 8px;
  overflow: hidden;
  /* Borde superior 4px — token neutro por default, HSL por hue con .has-hue. */
  border-top: 4px solid rgb(var(--v-theme-border));
  transition: border-color 150ms ease, transform 80ms ease;

  &:hover {
    border-color: rgba(var(--v-theme-on-surface), 0.24);
  }

  &:active {
    transform: scale(0.99);
  }

  /* Si la categoría tiene color_hue seteado, sobreescribir el top border
     con el hue al 55% sat / 50% light (saturado, legible en ambos modos). */
  &.has-hue {
    border-top-color: hsl(var(--category-hue) 55% 50%);
  }

  /* Sprint 3.A.bis — tipografía comprimida tablet-first.
     Antes: nombre 1.25rem (20px), precio 1.125rem (18px). Con tiles de
     160px de ancho + 2 líneas de nombre, el espacio vertical pedía 180+
     de alto total. Ahora 0.875rem (14px) weight 500 en nombre, 0.875rem
     weight 700 en precio — los tiles entran en 140-160px alto con aire
     para el thumbnail 60%. El cajero sigue leyendo fácil a distancia
     normal de tablet; la jerarquía ahora la da el weight, no el size. */
  .product-name {
    font-weight: 500;
    font-size: 0.875rem;
    line-height: 1.25;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.5em;
  }

  .product-price {
    display: flex;
    gap: 0.5rem;
    align-items: baseline;
    font-size: 0.875rem;
    font-weight: 600;

    .original-price {
      text-decoration: line-through;
      color: rgba(var(--v-theme-on-surface-variant), 0.7);
      font-size: 0.875rem;
      font-weight: 400;
    }

    .selling-price {
      color: rgb(var(--v-theme-primary));
      font-weight: 700;
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
