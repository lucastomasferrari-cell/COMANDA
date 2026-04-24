import { computed, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { usePosModeStore } from '@/modules/pos/stores/posModeStore.ts'

/**
 * Modos UI del POS. Distintos de dining_option (columna de orders) —
 * posMode es la vista que el cajero ve en pantalla.
 *
 * Mapeo al dining_option de una orden creada desde cada modo:
 *   salon   → dine_in
 *   counter → counter
 *   orders  → takeout o delivery (según acción dentro del hub)
 */
export type PosMode = 'salon' | 'counter' | 'orders' | 'caja'

export interface PosFeatureFlags {
  dine_in: boolean
  counter: boolean
  takeout: boolean
  delivery: boolean
}

// Sprint 4 commit 5 — el "mode activo" es ahora el currentMode del
// store usePosModeStore. Este composable se quedó delegando al store
// + manteniendo la persistencia a localStorage + lógica de
// availableModes según feature flags. Es un thin wrapper sobre el
// store; los callers existentes no se dieron cuenta del cambio.
const STORAGE_KEY = 'comanda.pos.mode'

let restored = false

function getStoredMode (): PosMode | null {
  if (typeof window === 'undefined') return null
  const value = window.localStorage.getItem(STORAGE_KEY)
  if (value === 'salon' || value === 'counter' || value === 'orders' || value === 'caja') {
    return value
  }
  return null
}

function persist (mode: PosMode): void {
  if (typeof window === 'undefined') return
  window.localStorage.setItem(STORAGE_KEY, mode)
}

/**
 * Composable de acceso al modo activo. Internamente todo va contra el
 * Pinia store posModeStore — este composable mantiene la API histórica
 * (mode/availableModes/setMode/showSwitcher) + persistencia
 * localStorage + sanity check contra feature flags.
 *
 * Uso:
 *   const { mode, availableModes, setMode, showSwitcher } = usePosMode(flagsRef)
 *
 * Para tracking de pausedOrderId al cambiar modo, llamar
 * directamente al store: usePosModeStore().switchMode(next, orderId).
 */
export function usePosMode (flags: () => PosFeatureFlags | null | undefined) {
  const store = usePosModeStore()
  const { currentMode } = storeToRefs(store)

  // Restore del localStorage solo en la primera invocación de la
  // sesión. Después del restore, el watch de abajo persiste cualquier
  // cambio. El singleton flag evita doble-restore si se llama
  // usePosMode desde varios componentes.
  if (!restored) {
    const stored = getStoredMode()
    if (stored) store.currentMode = stored
    restored = true
  }

  // Persistir cualquier cambio de modo al localStorage. Sin esto, los
  // cambios via store.switchMode no sobreviven al refresh.
  watch(currentMode, next => persist(next))

  const availableModes = computed<PosMode[]>(() => {
    const f = flags()
    // 'caja' siempre disponible mientras haya un register — el POS asume
    // sesión de caja activa (si no, el viewer falla antes). No tiene
    // feature flag porque cerrar el acceso a caja desde UI rompería el
    // flujo operativo sin camino alternativo.
    if (!f) return ['salon', 'counter', 'orders', 'caja']
    const list: PosMode[] = []
    if (f.dine_in) list.push('salon')
    if (f.counter) list.push('counter')
    if (f.takeout || f.delivery) list.push('orders')
    list.push('caja')
    return list
  })

  // Si el modo activo deja de estar disponible (feature flag cambió),
  // reasignar a otro disponible. switchMode (vía store) sin
  // pausedOrderId — esto es un fallback automático, no un cambio
  // intencional del usuario.
  watch(availableModes, list => {
    if (list.length === 0) return
    if (!list.includes(currentMode.value)) {
      const stored = getStoredMode()
      const fallback = list[0] ?? 'salon'
      store.currentMode = stored && list.includes(stored) ? stored : fallback
    }
  }, { immediate: true })

  const setMode = (next: PosMode): void => {
    if (!availableModes.value.includes(next)) return
    store.switchMode(next)
  }

  const showSwitcher = computed(() => availableModes.value.length > 1)

  return {
    mode: currentMode,
    availableModes,
    setMode,
    showSwitcher,
  }
}
