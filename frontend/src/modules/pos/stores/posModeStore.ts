import type { PosMode } from '@/modules/pos/composables/posMode.ts'
import { defineStore } from 'pinia'

// Sprint 4 commit 4 — store Pinia para "memoria de modos" del POS.
// Cada modo lleva su propio estado: si quedó una orden pausada al
// cambiar a otro modo, qué timestamp tuvo la última actividad.
//
// En Phase A es in-memory only — pausedOrderId se trackea pero la
// continuidad de la orden depende de KeepAlive (cart compartido).
// En Phase B (drafts en backend) la orden se persiste en backend y
// resumePausedOrder() recargaría items + customer + datos previos.
//
// Multi-device del mismo user: scope por (user_id, register_id) cuando
// haya backend drafts. Hoy el store es local al browser; no sincroniza
// entre tabs (cada tab tiene su Pinia).

interface ModeState {
  /** ID de la orden que quedó pausada al salir de este modo, null si no hay. */
  pausedOrderId: number | string | null
  /** Items de la orden pausada (para el badge del switcher). null si no se conoce. */
  pausedItemsCount: number | null
  /** Timestamp último ingreso al modo (debug + futuro stale-detection). */
  lastActiveAt: number
}

interface PosModeStoreState {
  currentMode: PosMode
  modeStates: Record<PosMode, ModeState>
}

const emptyModeState = (): ModeState => ({
  pausedOrderId: null,
  pausedItemsCount: null,
  lastActiveAt: 0,
})

export const usePosModeStore = defineStore('posMode', {
  state: (): PosModeStoreState => ({
    currentMode: 'salon',
    modeStates: {
      salon: emptyModeState(),
      counter: emptyModeState(),
      orders: emptyModeState(),
      caja: emptyModeState(),
    },
  }),

  actions: {
    /**
     * Cambia de modo. Si el modo actual tenía orden activa, la marca
     * como pausada (guardando su ID) antes de saltar al siguiente.
     *
     * No persiste la orden en backend — eso es Phase B. Acá solo
     * trackeamos el ID en memoria.
     */
    switchMode(nextMode: PosMode, currentActiveOrderId?: number | string | null): void {
      if (nextMode === this.currentMode) return
      if (currentActiveOrderId) {
        this.modeStates[this.currentMode].pausedOrderId = currentActiveOrderId
        this.modeStates[this.currentMode].lastActiveAt = Date.now()
      }
      this.currentMode = nextMode
      this.modeStates[nextMode].lastActiveAt = Date.now()
    },

    /**
     * Devuelve el ID de la orden pausada del modo actual y la limpia
     * del store. El caller es responsable de cargar la orden con ese
     * ID en el cart (Phase B: vía edit endpoint sobre la draft persistida).
     */
    resumePausedOrder(): number | string | null {
      const state = this.modeStates[this.currentMode]
      const id = state.pausedOrderId
      state.pausedOrderId = null
      state.pausedItemsCount = null
      return id
    },

    /**
     * Consulta sin mutar — para banners y badges que muestran "tenés
     * orden pausada en modo X".
     */
    hasPausedOrder(mode?: PosMode): boolean {
      const m = mode ?? this.currentMode
      return this.modeStates[m].pausedOrderId !== null
    },

    /**
     * Descarta la orden pausada del modo actual sin resumirla. El
     * caller (banner "Descartar") debe haber confirmado con el user.
     * En Phase B también va a hacer DELETE /api/v1/orders/{id} sobre
     * la draft persistida.
     */
    discardPausedOrder(): void {
      const state = this.modeStates[this.currentMode]
      state.pausedOrderId = null
      state.pausedItemsCount = null
    },

    /**
     * Util para Phase B: setear el pausedOrderId de un modo arbitrario
     * (cuando restoreamos drafts del backend al boot del POS).
     */
    setPausedOrder(mode: PosMode, orderId: number | string | null): void {
      this.modeStates[mode].pausedOrderId = orderId
      if (orderId === null) {
        this.modeStates[mode].pausedItemsCount = null
      }
    },

    /**
     * Cache del item count para el badge del switcher. Lo escribe
     * PausedOrderBanner cuando carga la summary de la orden.
     */
    setPausedItemsCount(mode: PosMode, count: number | null): void {
      this.modeStates[mode].pausedItemsCount = count
    },
  },
})
