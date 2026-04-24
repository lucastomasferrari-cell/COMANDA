export type OrderHubStatus =
  | 'needs_approval'
  | 'in_kitchen'
  | 'ready'
  | 'in_transit'
  | 'completed'

interface OrderLike {
  status?: string | null
  closed_at?: string | null
  needs_approval?: boolean | null
  dispatched_at?: string | null
}

/**
 * Sprint 3.A — mapeo centralizado de (status + flags) → estado del hub.
 *
 * Decisión B del sprint planning: NO extender OrderStatus del vendor,
 * derivar el estado visible del hub acá. Si cambia el mapeo, se toca
 * SOLO este archivo.
 *
 * OrderStatus vendor: pending, confirmed, preparing, ready, served,
 * completed, cancelled, refunded, merged.
 *
 * Prioridad (override de arriba hacia abajo):
 *   1. needs_approval flag → siempre gana si está true
 *   2. closed_at != null OR status ∈ {completed, served, cancelled,
 *      refunded, merged} → 'completed' (estados finales)
 *   3. dispatched_at != null → 'in_transit' (solo delivery)
 *   4. status = 'ready' → 'ready'
 *   5. fallback → 'in_kitchen' (pending/confirmed/preparing)
 */
const FINAL_STATUSES = new Set([
  'completed',
  'served',
  'cancelled',
  'refunded',
  'merged',
])

export function useOrderHubStatus (order: OrderLike): OrderHubStatus {
  if (order.needs_approval === true) return 'needs_approval'

  const isFinal = order.closed_at != null
    || (typeof order.status === 'string' && FINAL_STATUSES.has(order.status))
  if (isFinal) return 'completed'

  if (order.dispatched_at != null) return 'in_transit'

  if (order.status === 'ready') return 'ready'

  return 'in_kitchen'
}

/**
 * Mapa de estado → label i18n key para las tabs del hub.
 * El componente lee t(label(status)) para mostrar.
 */
export function orderHubStatusLabel (status: OrderHubStatus): string {
  const map: Record<OrderHubStatus, string> = {
    needs_approval: 'pos::pos_viewer.orders_hub.status.needs_approval',
    in_kitchen: 'pos::pos_viewer.orders_hub.status.in_kitchen',
    ready: 'pos::pos_viewer.orders_hub.status.ready',
    in_transit: 'pos::pos_viewer.orders_hub.status.in_transit',
    completed: 'pos::pos_viewer.orders_hub.status.completed',
  }
  return map[status]
}
