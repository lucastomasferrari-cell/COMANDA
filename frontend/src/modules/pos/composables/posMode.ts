import { computed, ref, watch } from 'vue'

/**
 * Modos UI del POS. Distintos de dining_option (columna de orders) —
 * posMode es la vista que el cajero ve en pantalla.
 *
 * Mapeo al dining_option de una orden creada desde cada modo:
 *   salon   → dine_in
 *   counter → counter
 *   orders  → takeout o delivery (según acción dentro del hub)
 */
export type PosMode = 'salon' | 'counter' | 'orders'

export interface PosFeatureFlags {
  dine_in: boolean
  counter: boolean
  takeout: boolean
  delivery: boolean
}

const STORAGE_KEY = 'comanda.pos.mode'
const activeMode = ref<PosMode>('salon')

/**
 * Restaura el modo del último load si el usuario ya lo eligió. Si no
 * hay nada en localStorage o el modo guardado no está disponible, cae
 * al primer modo disponible en el orden salon > counter > orders.
 */
function restore (available: PosMode[]): PosMode {
  const stored = typeof window !== 'undefined'
    ? window.localStorage.getItem(STORAGE_KEY) as PosMode | null
    : null
  if (stored && available.includes(stored)) return stored
  return available[0] ?? 'salon'
}

function persist (mode: PosMode): void {
  if (typeof window === 'undefined') return
  window.localStorage.setItem(STORAGE_KEY, mode)
}

/**
 * Composable de acceso al modo activo.
 *
 * Uso:
 *   const { mode, availableModes, setMode, showSwitcher } = usePosMode(flagsRef)
 */
export function usePosMode (flags: () => PosFeatureFlags | null | undefined) {
  const availableModes = computed<PosMode[]>(() => {
    const f = flags()
    if (!f) return ['salon', 'counter', 'orders']
    const list: PosMode[] = []
    if (f.dine_in) list.push('salon')
    if (f.counter) list.push('counter')
    if (f.takeout || f.delivery) list.push('orders')
    return list
  })

  // Si el modo guardado ya no está disponible (flag cambió), reasignar.
  watch(availableModes, list => {
    if (list.length === 0) return
    if (!list.includes(activeMode.value)) {
      activeMode.value = restore(list)
      persist(activeMode.value)
    }
  }, { immediate: true })

  const setMode = (next: PosMode): void => {
    if (!availableModes.value.includes(next)) return
    activeMode.value = next
    persist(next)
  }

  // Si solo hay 1 modo disponible, no tiene sentido el switcher.
  const showSwitcher = computed(() => availableModes.value.length > 1)

  return {
    mode: activeMode,
    availableModes,
    setMode,
    showSwitcher,
  }
}
