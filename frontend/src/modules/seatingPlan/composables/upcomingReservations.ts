import { computed, onMounted, onBeforeUnmount, ref } from 'vue'
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

  const fetch = async (): Promise<void> => {
    loading.value = true
    try {
      const res = await http.get('/v1/reservations/upcoming')
      const body = res.data?.body ?? res.data ?? []
      reservations.value = Array.isArray(body) ? body : []
    } catch (err) {
      // El plano no se puede caer por una reserva que no cargó. Dejamos
      // lista vacía y seguimos; si el endpoint fallaba por 500 (ej: la
      // tabla reservations todavía no migró) no queremos bloquear el POS.
      // Warn visible en devtools para que un dev lo note sin que el user
      // vea nada roto.
      console.warn('[useUpcomingReservations] endpoint no disponible, usando lista vacía', err)
      reservations.value = []
    } finally {
      loading.value = false
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
    if (pollTimer !== null) {
      window.clearInterval(pollTimer)
    }
  })

  return { reservations, byTable, loading, refresh: fetch }
}
