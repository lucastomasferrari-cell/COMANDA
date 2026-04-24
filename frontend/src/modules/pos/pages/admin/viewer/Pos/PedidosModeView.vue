<script lang="ts" setup>
  import { nextTick } from 'vue'
  import { useI18n } from 'vue-i18n'

  // Sprint 3.A.bis bug 2 — Placeholder del modo Pedidos. Sprint 3.C va a
  // reemplazarlo por el Orders Hub: grid de pedidos takeout/delivery con
  // filtros por channel (propio/teléfono/whatsapp) + acciones approve /
  // mark-ready / assign-driver / mark-delivered. El composable
  // useOrderHubStatus del Sprint 3.A ya espera este modo.

  const emit = defineEmits<{
    (e: 'new-order'): void
  }>()

  const { t } = useI18n()

  // Ver MostradorModeView para la razón del nextTick + ripple=false: este
  // componente es un v-else-if branch que se desmonta cuando
  // hasActiveOrder flippea a true; sin el defer, el ciclo de click del
  // VBtn choca con el unmount y Vue crashea accediendo __vnode null.
  const onClickNew = async (): Promise<void> => {
    await nextTick()
    emit('new-order')
  }
</script>

<template>
  <div class="pedidos-placeholder">
    <div class="icon-wrapper mb-4">
      <VIcon color="primary" icon="tabler-clipboard-list" size="48" />
    </div>
    <h3 class="text-h6 font-weight-bold mb-2">
      {{ t('pos::pos_viewer.modes_placeholders.pedidos.title') }}
    </h3>
    <p class="text-body-2 text-medium-emphasis text-center mb-6" style="max-width: 460px;">
      {{ t('pos::pos_viewer.modes_placeholders.pedidos.description') }}
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
  </div>
</template>

<style lang="scss" scoped>
.pedidos-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgb(var(--v-theme-surface));
  border-radius: 8px;
  padding: 48px 32px;
  height: 100%;
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
