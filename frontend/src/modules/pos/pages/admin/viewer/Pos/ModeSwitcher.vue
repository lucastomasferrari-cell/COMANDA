<script lang="ts" setup>
  import type { PosMode } from '@/modules/pos/composables/posMode.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { usePosModeStore } from '@/modules/pos/stores/posModeStore.ts'

  const props = defineProps<{
    modelValue: PosMode
    availableModes: PosMode[]
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', mode: PosMode): void
  }>()

  const { t } = useI18n()
  // Sprint 4 commit 8 — el store expone modeStates por modo; cada
  // ícono renderiza un badge si el modo correspondiente tiene una
  // orden pausada. Si conocemos el item count (cacheado por el banner
  // al fetchear el summary), lo mostramos. Sino mostramos un dot.
  const store = usePosModeStore()

  // Sprint 3.A — Switcher vertical siempre visible al extremo izq del POS.
  // Sprint 3.A.bis — comprimido a 64px por botón (antes 72) + ícono 24px
  // + label 11px + activo con bg sutil + left indicator coral (no cuadro
  // relleno saturado). Toast-style.
  const modes = computed(() => ([
    {
      key: 'salon' as PosMode,
      icon: 'tabler-building-skyscraper',
      label: t('pos::pos_viewer.modes.salon'),
    },
    {
      key: 'counter' as PosMode,
      icon: 'tabler-bolt',
      label: t('pos::pos_viewer.modes.counter'),
    },
    {
      // Sprint 3.A.bis bug 4 — tabler-package renderizaba como cubo 3D
      // (visualmente distinto del resto de íconos outline del switcher).
      // tabler-clipboard-list es consistente con Salón/Mostrador/Caja:
      // línea fina, estilo Toast-like, hereda el color del parent.
      key: 'orders' as PosMode,
      icon: 'tabler-clipboard-list',
      label: t('pos::pos_viewer.modes.orders'),
    },
    {
      key: 'caja' as PosMode,
      icon: 'tabler-cash-register',
      label: t('pos::pos_viewer.modes.caja'),
    },
  ].filter(m => props.availableModes.includes(m.key))))

  const selectMode = (mode: PosMode): void => {
    emit('update:modelValue', mode)
  }

  const hasPaused = (mode: PosMode): boolean =>
    store.modeStates[mode].pausedOrderId !== null

  const pausedCount = (mode: PosMode): number | null =>
    store.modeStates[mode].pausedItemsCount
</script>

<template>
  <nav class="mode-switcher" aria-label="POS mode">
    <button
      v-for="m in modes"
      :key="m.key"
      type="button"
      class="mode-switcher__btn"
      :class="{ 'mode-switcher__btn--active': modelValue === m.key }"
      :aria-pressed="modelValue === m.key"
      :aria-label="m.label"
      @click="selectMode(m.key)"
    >
      <span class="mode-switcher__icon-wrap">
        <VIcon class="mode-switcher__icon" :icon="m.icon" size="24" />
        <span
          v-if="hasPaused(m.key)"
          class="mode-switcher__badge"
          :class="{ 'mode-switcher__badge--dot': pausedCount(m.key) === null }"
        >{{ pausedCount(m.key) ?? '' }}</span>
      </span>
      <span class="mode-switcher__label">{{ m.label }}</span>
    </button>
  </nav>
</template>

<style lang="scss" scoped>
/* Sprint 3.A.bis — switcher compacto tablet-first.
   Ancho 80px total, cada botón 64px alto (touch ≥44px ✓), ícono 24px,
   label 11px. Activo con bg sutil + border-left coral indicator en vez
   del cuadrote coral saturado anterior — Toast-style discreto. */
.mode-switcher {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 8px;
  width: 80px;
  padding: 12px 8px;
  background: rgb(var(--v-theme-surface));
  border-right: 1px solid rgb(var(--v-theme-border));
  flex-shrink: 0;
}

.mode-switcher__btn {
  all: unset;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-height: 64px;
  padding: 8px 4px;
  border-radius: 10px;
  cursor: pointer;
  color: rgb(var(--v-theme-on-surface-variant));
  transition:
    background-color 200ms ease,
    color 200ms ease,
    transform 120ms ease;

  &:hover {
    background: rgb(var(--v-theme-surface-variant));
    color: rgb(var(--v-theme-on-surface));
  }

  &:active {
    transform: scale(0.97);
  }

  &:focus-visible {
    outline: 2px solid rgb(var(--v-theme-primary));
    outline-offset: 2px;
  }
}

.mode-switcher__btn--active {
  background: rgba(var(--v-theme-primary), 0.08);
  color: rgb(var(--v-theme-primary));

  &:hover {
    background: rgba(var(--v-theme-primary), 0.12);
    color: rgb(var(--v-theme-primary));
  }

  /* Left indicator coral — barra 3px a la izquierda. Toast-style:
     activo discreto sin invertir colores; el indicador vertical marca
     dónde estás sin robar protagonismo visual. */
  &::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 8px;
    bottom: 8px;
    width: 3px;
    border-radius: 0 2px 2px 0;
    background: rgb(var(--v-theme-primary));
  }
}

.mode-switcher__icon-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.mode-switcher__icon {
  flex-shrink: 0;
}

/* Badge "orden pausada" — top-right del ícono. Con count se ve como
   chip rojo pequeño con número; sin count (--dot) se reduce a un
   punto coral discreto. Se mantiene legible en dark mode porque
   usa rgb(var(--v-theme-error)) y on-error como tokens. */
.mode-switcher__badge {
  position: absolute;
  top: -4px;
  right: -8px;
  min-width: 16px;
  height: 16px;
  padding: 0 4px;
  border-radius: 8px;
  background: rgb(var(--v-theme-error));
  color: rgb(var(--v-theme-on-error));
  font-size: 0.625rem; /* 10px */
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  box-shadow: 0 0 0 2px rgb(var(--v-theme-surface));
}

.mode-switcher__badge--dot {
  min-width: 8px;
  height: 8px;
  padding: 0;
  top: -2px;
  right: -2px;
}

.mode-switcher__label {
  font-size: 0.6875rem; /* 11px — spec tablet compacta */
  font-weight: 600;
  letter-spacing: 0.02em;
  text-align: center;
  line-height: 1.1;
}
</style>
