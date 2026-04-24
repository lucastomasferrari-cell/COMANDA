<script lang="ts" setup>
  import type { PosMode } from '@/modules/pos/composables/posMode.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    modelValue: PosMode
    availableModes: PosMode[]
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', mode: PosMode): void
  }>()

  const { t } = useI18n()

  // Sprint 3.A — Switcher vertical siempre visible al extremo izq del POS.
  // Copia exacta del patrón Toast: 3 íconos grandes, label debajo, activo
  // con fondo coral. Si solo 1 modo disponible, Pos/Index.vue no lo
  // monta (componente asume que availableModes.length >= 2).
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
      key: 'orders' as PosMode,
      icon: 'tabler-package',
      label: t('pos::pos_viewer.modes.orders'),
    },
  ].filter(m => props.availableModes.includes(m.key))))

  const selectMode = (mode: PosMode): void => {
    emit('update:modelValue', mode)
  }
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
      <VIcon class="mode-switcher__icon" :icon="m.icon" size="28" />
      <span class="mode-switcher__label">{{ m.label }}</span>
    </button>
  </nav>
</template>

<style lang="scss" scoped>
.mode-switcher {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 6px;
  width: 80px;
  padding: 12px 8px;
  background: rgb(var(--v-theme-surface));
  border-right: 1px solid rgb(var(--v-theme-border));
  flex-shrink: 0;
}

.mode-switcher__btn {
  all: unset;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-height: 72px;
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
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));

  &:hover {
    background: rgb(var(--v-theme-primary));
    color: rgb(var(--v-theme-on-primary));
  }
}

.mode-switcher__icon {
  flex-shrink: 0;
}

.mode-switcher__label {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-align: center;
  line-height: 1.1;
}
</style>
