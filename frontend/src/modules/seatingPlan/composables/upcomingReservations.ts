import axios from 'axios'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { http } from '@/modules/core/api/http.ts'

export interface UpcomingReservation {
  id: number
  table_id: number
  guest_name: string | null
  guest_phone: string | null
  party_size: number
  reserved_for: string
  status: 'pending' | 'confirmed'
  notes: string | null
}

/**
 * Sprint 3.A.bis — lista de reservas próximas (2hs) indexada por
 * table_id para que el plano pinte un badge en las mesas que tienen
 * una reserva inminente.
 *
 * Hoy el flujo end-to-end no existe: las reservas se crean solo via
 * seeder demo (PASE Fase 2 va a construir el UI de crear/editar).
 * Este composable prepara el camino.
 */
export function useUpcomingReservations () {
  const reservations = ref<UpcomingReservation[]>([])
  const loading = ref(false)
  let pollTimer: number | null = null
  // Sprint 3.A.bis post-validación 3 — abortController + isAlive guard
  // contra mutaciones post-unmount. En el POS, el plano (consumidor de
  // este composable) vive dentro de Salón; al cambiar a Mostrador/
  // Pedidos/Caja el TablePlano se desmonta mientras puede haber un
  // fetch en vuelo. Si la response llegaba después, mutabamos refs de
  // un tree ya desmontado y Vue crasheaba con __vnode null.
  let abortController: AbortController | null = null
  let isAlive = true

  const fetch = async (): Promise<void> => {
    if (!isAlive) return
    // Cancelar cualquier fetch anterior en vuelo — si el poll se dispara
    // dos veces solapadas (manualmente + intervalo), solo vale la última.
    abortController?.abort()
    abortController = new AbortController()
    loading.value = true
    try {
      const res = await http.get('/v1/reservations/upcoming', {
        signal: abortController.signal,
      })
      if (!isAlive) return
      const body = res.data?.body ?? res.data ?? []
      reservations.value = Array.isArray(body) ? body : []
    } catch (err) {
      // Cancelación silenciosa: no es un error del endpoint, es que
      // nos desmontaron o llegó un fetch más nuevo. No warn, no reset.
      if (axios.isCancel(err) || (err as any)?.name === 'CanceledError') {
        return
      }
      if (!isAlive) return
      // El plano no se puede caer por una reserva que no cargó. Dejamos
      // lista vacía y seguimos; si el endpoint fallaba por 500 no
      // queremos bloquear el POS. Warn visible en devtools.
      console.warn('[useUpcomingReservations] endpoint no disponible, usando lista vacía', err)
      reservations.value = []
    } finally {
      if (isAlive) loading.value = false
    }
  }

  // Mapa table_id → reserva próxima. Si hay múltiples en la misma mesa,
  // elige la más cercana en tiempo (primera del sort por reserved_for).
  const byTable = computed<Record<number, UpcomingReservation>>(() => {
    const map: Record<number, UpcomingReservation> = {}
    for (const r of reservations.value) {
      if (r.table_id && !map[r.table_id]) {
        map[r.table_id] = r
      }
    }
    return map
  })

  onMounted(() => {
    fetch()
    // Refresh cada 60s para que el badge se mantenga actualizado.
    pollTimer = window.setInterval(fetch, 60_000)
  })

  onBeforeUnmount(() => {
    isAlive = false
    if (pollTimer !== null) {
      window.clearInterval(pollTimer)
    }
    abortController?.abort()
  })

  return { reservations, byTable, loading, refresh: fetch }
}
