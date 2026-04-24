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
       categoría tiene color_hue. Sin hue → cae al border token neutro.
       Sprint 3.A.bis bug 3: altura cappeada 150px total con layout
       imagen/body 60/40 para encajar tiles compactos tablet-first. -->
  <VCard
    class="product-item"
    :class="{ 'has-hue': categoryHue !== null }"
    :ripple="false"
    :style="{ '--category-hue': categoryHue ?? 12 }"
    @click="addProductToCart"
  >
    <!-- ProductTileImage reemplaza el VImg + VIcon tabler-soup genérico
         (anti-patrón: todos los productos mostraban la misma sopita).
         Ahora foto si existe o iniciales tinteadas por el hue de la categoría. -->
    <div class="product-item__image">
      <ProductTileImage
        :category-color-hue="categoryHue"
        :name="product.name"
        :thumbnail="product.thumbnail"
      />
      <div v-if="product.is_new" class="new-badge">
        {{ t('pos::pos_viewer.new').toUpperCase() }}
      </div>
    </div>
    <div class="product-item__body">
      <div class="product-name">{{ product.name }}</div>
      <div class="product-price">
        <span v-if="hasDiscount" class="selling-price">
          {{ product.selling_price?.formatted }}
        </span>
        <span :class="{ 'original-price': hasDiscount }">
          {{ product.price?.formatted }}
        </span>
      </div>
    </div>
  </VCard>
</template>

<style lang="scss" scoped>
/* Sprint 3.A.bis bug 3 — tiles cappeados 140-160px total con layout
   imagen 60% / body 40%. El commit 70541f8 había bajado las fuentes
   (14px) pero el v-card-text seguía con padding default + sin altura
   fija, así que los tiles crecían a ~200-250px en productos con
   nombres largos. Ahora:
     - Card: height 150px fijo (max 160 en cards con nombre 2 líneas).
     - Imagen: flex-basis 60% sin aspect-ratio (lo fija el height).
     - Body: flex-basis 40%, padding 8px 10px, distribuye con
       justify-content: space-between para que precio siempre quede
       abajo.
   Criterio validado: 1280px viewport, split-screen working → columna
   main ~615px, gap 10px, minmax(160px, 1fr) → 3 cols cómodos con tiles
   totales de 180×150px. */
.product-item {
  cursor: pointer;
  display: flex;
  flex-direction: column;
  height: 150px;
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
}

.product-item__image {
  position: relative;
  flex: 0 0 60%;
  overflow: hidden;
  /* Asegurar que el VImg/placeholder respete el height del flex-basis:
     sin esto, el aspect-ratio 1.4 del ProductTileImage le gana. */
  :deep(.product-tile-image) {
    height: 100%;
    aspect-ratio: auto;
    border-radius: 0;
  }
  :deep(.product-tile-image__placeholder) {
    font-size: 1.5rem; /* placeholder 24px — compacto para 60% de 150px */
  }
}

.product-item__body {
  flex: 1 1 40%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 6px 10px 8px;
  min-height: 0;
}

/* Nombre 13px line-height 1.2 clamp 2 — evita overflow vertical en
   productos con nombres largos. Jerarquía por weight, no por size. */
.product-name {
  font-weight: 500;
  font-size: 0.8125rem;
  line-height: 1.2;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  word-break: break-word;
}

.product-price {
  display: flex;
  gap: 6px;
  align-items: baseline;
  font-size: 0.875rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
  line-height: 1;

  .original-price {
    text-decoration: line-through;
    color: rgba(var(--v-theme-on-surface-variant), 0.7);
    font-size: 0.75rem;
    font-weight: 400;
  }

  .selling-price {
    color: rgb(var(--v-theme-primary));
    font-weight: 700;
  }
}

.new-badge {
  position: absolute;
  top: 6px;
  right: 6px;
  background-color: rgb(var(--v-theme-error));
  color: white;
  padding: 2px 6px;
  font-size: 0.625rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  border-radius: 4px;
  z-index: 1;
}
</style>
