<script lang="ts" setup>
  import { computed, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import { usePosModeStore } from '@/modules/pos/stores/posModeStore.ts'

  // Sprint 4 commit 6 — banner que aparece arriba de la home de cada
  // modo cuando ese modo tiene una orden pausada en el store. Ofrece
  // dos acciones:
  //   Continuar → recarga la orden en el cart vía edit endpoint, emite
  //     init-order al padre (que la abre en el split-screen working).
  //     El store limpia el pausedOrderId al confirmar.
  //   Descartar → solo limpia el store (Phase A in-memory). En Phase B
  //     va a hacer DELETE /api/v1/orders/{id} sobre la draft real.
  //
  // Resolución del summary: hace getShowData del orderId para mostrar
  // items_count + total. Si el orden ya no existe (404), el store se
  // limpia automático — caso típico cuando otro cajero la cobró/canceló
  // desde otro device.

  const props = defineProps<{
    cartId: string
  }>()

  const emit = defineEmits<{
    (e: 'init-order', response: Record<string, any>): void
  }>()

  const { t } = useI18n()
  const toast = useToast()
  const store = usePosModeStore()
  const { getShowData, edit } = useOrder()

  const orderSummary = ref<Record<string, any> | null>(null)
  const loadingResume = ref(false)
  let isAlive = true

  const pausedOrderId = computed(() => store.modeStates[store.currentMode].pausedOrderId)

  const itemsCount = computed<number>(() => {
    const items = orderSummary.value?.items
    return Array.isArray(items) ? items.length : 0
  })

  const totalLabel = computed<string>(() => {
    return orderSummary.value?.total?.formatted ?? ''
  })

  async function fetchSummary (): Promise<void> {
    const id = pausedOrderId.value
    if (!id) {
      orderSummary.value = null
      return
    }
    try {
      const res = await getShowData(id)
      if (!isAlive) return
      if (res.status === 200 && res.data) {
        orderSummary.value = res.data
      } else {
        // 404 / desaparecida — limpiar la referencia stale del store
        // así no queda colgado el banner sin data.
        store.discardPausedOrder()
        orderSummary.value = null
      }
    } catch {
      // Silencio en summary fallback — si el banner no puede mostrar
      // detalles, al menos no rompe la home. El user igual puede
      // tocar Continuar (que reintenta vía edit) o Descartar.
    }
  }

  watch(pausedOrderId, fetchSummary, { immediate: true })

  async function onContinue (): Promise<void> {
    const id = pausedOrderId.value
    if (!id || loadingResume.value) return
    loadingResume.value = true
    try {
      const response = (await edit(props.cartId, id)).data.body
      if (!isAlive) return
      // Limpiamos la referencia del store: la orden ahora vive en el
      // cart compartido (será el activo del modo cuando emit init-order
      // setee meta.order y newOrderStarted).
      store.discardPausedOrder()
      orderSummary.value = null
      emit('init-order', response)
    } catch (err: any) {
      if (!isAlive) return
      toast.error(err?.response?.data?.message ?? t('core::errors.an_unexpected_error_occurred'))
    } finally {
      if (isAlive) loadingResume.value = false
    }
  }

  function onDiscard (): void {
    // Phase A: solo limpia el store. La orden queda en backend con su
    // status original (pending/confirmed/etc). Phase B va a agregar
    // DELETE sobre las drafts puras (status='draft').
    store.discardPausedOrder()
    orderSummary.value = null
  }

  // Ya no llamamos isAlive=false en onUnmounted — con KeepAlive el
  // banner no se desmonta. La flag igual sirve si en algún caller raro
  // sí desmonta (edge case del routing).
</script>

<template>
  <div v-if="pausedOrderId" class="paused-banner">
    <VIcon class="paused-banner__icon" color="primary" icon="tabler-clock-pause" size="20" />
    <div class="paused-banner__body flex-grow-1">
      <div class="paused-banner__title text-body-2 font-weight-medium">
        {{ t('pos::pos_viewer.paused_order.title') }}
      </div>
      <div v-if="orderSummary" class="paused-banner__meta text-caption text-medium-emphasis">
        <span v-if="itemsCount > 0">{{ itemsCount }} {{ t('pos::pos_viewer.paused_order.items') }}</span>
        <span v-if="itemsCount > 0 && totalLabel"> · </span>
        <span v-if="totalLabel">{{ totalLabel }}</span>
      </div>
    </div>
    <VBtn
      color="primary"
      :loading="loadingResume"
      size="small"
      variant="flat"
      @click="onContinue"
    >
      {{ t('pos::pos_viewer.paused_order.continue') }}
    </VBtn>
    <VBtn
      color="default"
      :disabled="loadingResume"
      size="small"
      variant="text"
      @click="onDiscard"
    >
      {{ t('pos::pos_viewer.paused_order.discard') }}
    </VBtn>
  </div>
</template>

<style lang="scss" scoped>
.paused-banner {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: rgba(var(--v-theme-primary), 0.08);
  border-left: 3px solid rgb(var(--v-theme-primary));
  border-radius: 6px;
  margin-bottom: 8px;
}

.paused-banner__icon {
  flex-shrink: 0;
}

.paused-banner__body {
  min-width: 0;
}

.paused-banner__title {
  color: rgb(var(--v-theme-on-surface));
  line-height: 1.2;
}

.paused-banner__meta {
  margin-top: 2px;
}
</style>
