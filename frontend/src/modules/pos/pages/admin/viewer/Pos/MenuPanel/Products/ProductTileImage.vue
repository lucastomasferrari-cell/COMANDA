<script lang="ts" setup>
  import { computed } from 'vue'

  // Placeholder inteligente Sprint 1.B BENCHMARK_POS #5:
  // Antes todos los productos sin foto mostraban el mismo VIcon
  // tabler-soup — anti-patrón grave: Stella Artois, Malbec, Flan mixto
  // compartían el ícono de sopa. El cajero tiene que leer el nombre
  // ENTERO cada vez porque la imagen no discrimina.
  //
  // Ahora: si hay thumbnail → VImg. Si no → placeholder con 2-3 letras
  // iniciales del nombre en background tinteado por el hue de la
  // categoría. "ST" verde-oliva para Stella, "MA" vino-tinto para Malbec,
  // "FL" rosado para Flan. Discriminable a distancia.
  const props = defineProps<{
    name: string
    thumbnail?: string | null
    categoryColorHue?: number | null
  }>()

  const initials = computed(() => {
    // Descartar stopwords típicas en nombres de productos AR (artículos,
    // conectores) para no sacar "DE" para "Bife de chorizo" o "LA" para
    // "Suprema a la napolitana". Tomar 2 primeras palabras significativas.
    const stopWords = new Set(['de', 'la', 'el', 'con', 'sin', 'al', 'a', 'y', 'o'])
    const words = (props.name ?? '')
      .split(/\s+/)
      .filter(w => w.length > 0 && !stopWords.has(w.toLowerCase()))
      .slice(0, 2)

    if (words.length === 0) return (props.name ?? '??').slice(0, 2).toUpperCase()
    if (words.length === 1) return (words[0] ?? '').slice(0, 2).toUpperCase()
    return ((words[0]?.[0] ?? '') + (words[1]?.[0] ?? '')).toUpperCase()
  })

  // Default hue 12 (coral marca) cuando la categoría no tiene color_hue
  // asignado o el producto no tiene categoría. Saturación/luminosidad
  // las fija el sistema (no accesibles por UI) para que el placeholder
  // sea siempre distinguible sin depender de taste del admin.
  const hue = computed(() => props.categoryColorHue ?? 12)
</script>

<template>
  <div
    class="product-tile-image"
    :style="{ '--placeholder-hue': hue }"
  >
    <VImg
      v-if="thumbnail"
      aspect-ratio="1.4"
      cover
      :src="thumbnail"
    />
    <div
      v-else
      class="product-tile-image__placeholder"
    >
      {{ initials }}
    </div>
  </div>
</template>

<style lang="scss" scoped>
.product-tile-image {
  aspect-ratio: 1.4;
  overflow: hidden;
  border-radius: 8px 8px 0 0;
  background: rgb(var(--v-theme-surface-variant));
}

/* Placeholder: fondo claro con hue de la categoría, texto oscuro del
   mismo hue para buen contraste. En dark mode (selector global abajo)
   se invierte: fondo oscuro saturado, texto claro. */
.product-tile-image__placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  background: hsl(var(--placeholder-hue) 40% 85%);
  color: hsl(var(--placeholder-hue) 50% 25%);
  user-select: none;
}
</style>

<style lang="scss">
/* Selector global (no scoped) para que .v-theme--dark del body
   matchee correctamente sobre el descendant scoped. */
.v-theme--dark .product-tile-image__placeholder {
  background: hsl(var(--placeholder-hue, 12) 30% 25%);
  color: hsl(var(--placeholder-hue, 12) 40% 80%);
}
</style>
