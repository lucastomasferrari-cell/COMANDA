<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  // Sprint 3.A.bis bug 2 — Placeholder del modo Mostrador. Sprint 3.C va
  // a llenarlo con el panel izq "Tabs abiertos" (counter orders activas)
  // + center con teclado numérico rápido o grid de favoritos. Hoy solo
  // expone la acción "+ Nueva" para que el cajero pueda seguir operando.

  defineEmits<{
    (e: 'new-order'): void
  }>()

  const { t } = useI18n()
</script>

<template>
  <div class="mostrador-placeholder">
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
        size="large"
        @click="$emit('new-order')"
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
