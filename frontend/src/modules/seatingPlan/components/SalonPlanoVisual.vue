<script lang="ts" setup>
  import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

  export interface PlanoTable {
    id: number
    name: string
    shape?: 'circle' | 'rectangle' | 'square' | null
    position_x: number | null
    position_y: number | null
    width: number
    height: number
    rotation: number
    status: { id: string, name?: string, color?: string } | null
    capacity?: number
    active_order?: {
      id: number
      guest_count?: number | null
      total?: { formatted?: string, amount?: number } | null
      created_at?: string | null
    } | null
  }

  export interface PlanoReservation {
    id: number
    table_id: number
    guest_name: string | null
    guest_phone: string | null
    party_size: number
    reserved_for: string
    status: 'pending' | 'confirmed'
    notes: string | null
  }

  const props = withDefaults(defineProps<{
    tables: PlanoTable[]
    editable?: boolean
    selectedId?: number | null
    snap?: boolean
    // Palette overrides por status id — si el backend no marda bill_requested
    // o paused, el componente no los pinta distinto.
    statusColors?: Partial<Record<string, string>>
    // Sprint 3.A.bis — mapa table_id → reserva próxima (2hs). Cuando
    // está presente, la mesa muestra un badge discreto púrpura.
    reservationsByTable?: Record<number, PlanoReservation>
  }>(), {
    editable: false,
    selectedId: null,
    snap: false,
    statusColors: () => ({}),
    reservationsByTable: () => ({}),
  })

  const emit = defineEmits<{
    (e: 'click-free', table: PlanoTable): void
    (e: 'click-occupied', table: PlanoTable): void
    (e: 'context-menu', payload: { table: PlanoTable, x: number, y: number }): void
    (e: 'move', payload: { id: number, position_x: number, position_y: number }): void
    (e: 'select', id: number): void
  }>()

  // ------ viewport (pan + zoom) ----------------------------------------
  const wrapperRef = ref<HTMLDivElement | null>(null)
  const zoom = ref(1)
  const panX = ref(0)
  const panY = ref(0)
  const panning = ref(false)
  const panStart = ref<{ x: number, y: number, panX: number, panY: number } | null>(null)

  const onWheel = (ev: WheelEvent) => {
    ev.preventDefault()
    const delta = -ev.deltaY * 0.0015
    const next = clamp(zoom.value + delta, 0.4, 2.5)
    zoom.value = next
  }

  const onBackgroundPointerDown = (ev: PointerEvent) => {
    if (ev.button !== 0) return
    if ((ev.target as HTMLElement).closest('.plano-table')) return
    panning.value = true
    panStart.value = { x: ev.clientX, y: ev.clientY, panX: panX.value, panY: panY.value }
    ;(ev.currentTarget as HTMLElement).setPointerCapture(ev.pointerId)
  }

  const onBackgroundPointerMove = (ev: PointerEvent) => {
    if (!panning.value || !panStart.value) return
    panX.value = panStart.value.panX + (ev.clientX - panStart.value.x)
    panY.value = panStart.value.panY + (ev.clientY - panStart.value.y)
  }

  const onBackgroundPointerUp = (ev: PointerEvent) => {
    panning.value = false
    panStart.value = null
    ;(ev.currentTarget as HTMLElement).releasePointerCapture?.(ev.pointerId)
  }

  // ------ auto-distribucion cuando tables no tienen x/y ----------------
  // Devuelve tables con position_x/y resuelto (auto-grid si es null).
  const resolvedTables = computed<PlanoTable[]>(() => {
    const unplaced = props.tables.filter(t => t.position_x == null || t.position_y == null)
    const placed = props.tables.filter(t => t.position_x != null && t.position_y != null)
    if (unplaced.length === 0) return placed
    const cols = Math.max(1, Math.ceil(Math.sqrt(unplaced.length)))
    const spacing = 120
    const startX = 80
    const startY = 80
    const autoPlaced = unplaced.map((t, i) => ({
      ...t,
      position_x: startX + (i % cols) * spacing,
      position_y: startY + Math.floor(i / cols) * spacing,
    }))
    return [...placed, ...autoPlaced]
  })

  // ------ drag de mesas (solo editable) --------------------------------
  const dragging = ref<{ id: number, offsetX: number, offsetY: number } | null>(null)
  // Overrides locales mientras se arrastra: committeamos al backend via emit('move') al soltar.
  const localOverrides = ref<Record<number, { position_x: number, position_y: number }>>({})

  const tableOf = (id: number): PlanoTable | undefined => resolvedTables.value.find(t => t.id === id)

  const effectivePos = (t: PlanoTable) => {
    const o = localOverrides.value[t.id]
    return {
      x: o ? o.position_x : (t.position_x ?? 0),
      y: o ? o.position_y : (t.position_y ?? 0),
    }
  }

  const onTablePointerDown = (ev: PointerEvent, table: PlanoTable) => {
    if (!props.editable || ev.button !== 0) return
    ev.stopPropagation()
    const pt = svgPoint(ev)
    const { x, y } = effectivePos(table)
    dragging.value = { id: table.id, offsetX: pt.x - x, offsetY: pt.y - y }
    emit('select', table.id)
    ;(ev.currentTarget as SVGElement).setPointerCapture(ev.pointerId)
  }

  const onTablePointerMove = (ev: PointerEvent) => {
    if (!dragging.value) return
    const pt = svgPoint(ev)
    let nx = pt.x - dragging.value.offsetX
    let ny = pt.y - dragging.value.offsetY
    if (props.snap) {
      nx = Math.round(nx / 20) * 20
      ny = Math.round(ny / 20) * 20
    }
    localOverrides.value[dragging.value.id] = { position_x: nx, position_y: ny }
  }

  const onTablePointerUp = (ev: PointerEvent) => {
    if (!dragging.value) return
    const id = dragging.value.id
    const final = localOverrides.value[id]
    if (final) emit('move', { id, position_x: final.position_x, position_y: final.position_y })
    dragging.value = null
    ;(ev.currentTarget as SVGElement).releasePointerCapture?.(ev.pointerId)
  }

  // svgPoint convierte client coords a coords del sistema SVG, respetando pan/zoom.
  const svgRef = ref<SVGSVGElement | null>(null)
  const svgPoint = (ev: PointerEvent): { x: number, y: number } => {
    if (!svgRef.value) return { x: ev.clientX, y: ev.clientY }
    const pt = svgRef.value.createSVGPoint()
    pt.x = ev.clientX
    pt.y = ev.clientY
    const ctm = (svgRef.value.getScreenCTM?.())
    if (!ctm) return { x: ev.clientX, y: ev.clientY }
    const inv = ctm.inverse()
    const res = pt.matrixTransform(inv)
    return { x: res.x, y: res.y }
  }

  // ------ click simple (no drag) ---------------------------------------
  const onTableClick = (table: PlanoTable) => {
    if (dragging.value) return
    const isOccupied = !!table.active_order || (!!table.status && table.status.id !== 'available')
    if (isOccupied) emit('click-occupied', table)
    else emit('click-free', table)
  }

  const onTableContextMenu = (ev: MouseEvent, table: PlanoTable) => {
    ev.preventDefault()
    emit('context-menu', { table, x: ev.clientX, y: ev.clientY })
  }

  // ------ colores por status -------------------------------------------
  const defaultStatusColor = (statusId: string | undefined | null): string => {
    switch (statusId) {
      case 'available': return '#a8e6cf'
      case 'occupied': return '#2ecc71'
      case 'reserved': return '#74b9ff'
      case 'cleaning': return '#bdc3c7'
      case 'merged': return '#9b59b6'
      case 'bill_requested': return '#e74c3c'
      case 'paused': return '#7f8c8d'
      default: return '#ecf0f1'
    }
  }
  const fillForTable = (t: PlanoTable): string => {
    const id = t.status?.id
    const override = id ? props.statusColors[id] : undefined
    return override ?? defaultStatusColor(id)
  }

  // tiempo transcurrido: recompute cada 30s (parent suele hacer polling similar)
  const now = ref(Date.now())
  let clockTimer: number | null = null
  onMounted(() => {
    clockTimer = window.setInterval(() => { now.value = Date.now() }, 30_000)
  })
  onBeforeUnmount(() => { if (clockTimer !== null) window.clearInterval(clockTimer) })

  const elapsedLabel = (iso?: string | null): string => {
    if (!iso) return ''
    const diff = now.value - new Date(iso).getTime()
    const minutes = Math.max(0, Math.floor(diff / 60_000))
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    if (h > 0) return `${h}:${m.toString().padStart(2, '0')}`
    return `0:${m.toString().padStart(2, '0')}`
  }

  // HH:mm local time del reserved_for. Lo mostramos así porque la reserva
  // "próxima" siempre es en las próximas 2hs → con hora alcanza.
  const reservationTimeLabel = (iso: string): string => {
    const d = new Date(iso)
    const hh = d.getHours().toString().padStart(2, '0')
    const mm = d.getMinutes().toString().padStart(2, '0')
    return `${hh}:${mm}`
  }

  const reservationTooltip = (r: PlanoReservation): string => {
    const who = r.guest_name ?? 'Sin nombre'
    const parts = [`${who} · x${r.party_size}`, reservationTimeLabel(r.reserved_for)]
    if (r.notes) parts.push(r.notes)
    return parts.join(' — ')
  }

  const reservationFor = (tableId: number): PlanoReservation | undefined => {
    return props.reservationsByTable[tableId]
  }

  // si cambia el set de tables (ids), limpio overrides locales de ids que
  // ya no existen — evita fugas si el parent hizo fetch y borro mesas.
  watch(() => props.tables.map(t => t.id).join(','), () => {
    const currentIds = new Set(props.tables.map(t => t.id))
    for (const id of Object.keys(localOverrides.value)) {
      if (!currentIds.has(Number(id))) delete localOverrides.value[Number(id)]
    }
  })

  function clamp (v: number, min: number, max: number): number {
    return Math.max(min, Math.min(max, v))
  }

  // helpers expuestos al padre (por ej. editor que quiere resetear)
  defineExpose({
    clearDragOverrides: () => { localOverrides.value = {} },
    resetView: () => { zoom.value = 1; panX.value = 0; panY.value = 0 },
  })
</script>

<template>
  <div
    ref="wrapperRef"
    class="plano-wrapper"
    :class="{ editable, panning }"
  >
    <svg
      ref="svgRef"
      class="plano-svg"
      viewBox="-400 -300 1600 1200"
      preserveAspectRatio="xMidYMid meet"
      @wheel.passive="onWheel"
      @pointerdown="onBackgroundPointerDown"
      @pointermove="onBackgroundPointerMove"
      @pointerup="onBackgroundPointerUp"
      @pointercancel="onBackgroundPointerUp"
    >
      <g :transform="`translate(${panX},${panY}) scale(${zoom})`">
        <!-- grid de fondo -->
        <defs>
          <pattern id="plano-grid" width="40" height="40" patternUnits="userSpaceOnUse">
            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(0,0,0,0.06)" stroke-width="1" />
          </pattern>
        </defs>
        <rect x="-2000" y="-1500" width="5000" height="4000" fill="url(#plano-grid)" />

        <g
          v-for="table in resolvedTables"
          :key="table.id"
          class="plano-table"
          :class="{
            selected: selectedId === table.id,
            editable,
            'has-reservation': !!reservationFor(table.id),
          }"
          :transform="`translate(${effectivePos(table).x},${effectivePos(table).y}) rotate(${table.rotation || 0})`"
          @pointerdown="onTablePointerDown($event, table)"
          @pointermove="onTablePointerMove"
          @pointerup="onTablePointerUp"
          @pointercancel="onTablePointerUp"
          @click.stop="onTableClick(table)"
          @contextmenu="onTableContextMenu($event, table)"
        >
          <!-- shape -->
          <ellipse
            v-if="(table.shape ?? 'circle') === 'circle'"
            :cx="0"
            :cy="0"
            :rx="table.width / 2"
            :ry="table.height / 2"
            :fill="fillForTable(table)"
            :stroke="reservationFor(table.id) ? '#8e44ad' : 'rgba(0,0,0,0.35)'"
            :stroke-width="reservationFor(table.id) ? 2 : 1.5"
          />
          <rect
            v-else
            :x="-table.width / 2"
            :y="-table.height / 2"
            :width="table.width"
            :height="table.height"
            rx="6"
            :fill="fillForTable(table)"
            :stroke="reservationFor(table.id) ? '#8e44ad' : 'rgba(0,0,0,0.35)'"
            :stroke-width="reservationFor(table.id) ? 2 : 1.5"
          />

          <!-- nombre -->
          <text
            class="table-name"
            :y="-6"
            text-anchor="middle"
            dominant-baseline="middle"
          >{{ table.name }}</text>

          <!-- detalles si ocupada -->
          <template v-if="table.active_order">
            <text
              class="table-elapsed"
              :y="14"
              text-anchor="middle"
              dominant-baseline="middle"
            >{{ elapsedLabel(table.active_order.created_at) }}<tspan v-if="table.active_order.guest_count"> · x{{ table.active_order.guest_count }}</tspan></text>
            <text
              v-if="table.active_order.total?.formatted"
              class="table-total"
              :y="30"
              text-anchor="middle"
              dominant-baseline="middle"
            >{{ table.active_order.total.formatted }}</text>
          </template>

          <!-- Sprint 3.A.bis — badge reserva próxima (top-right, offset
               hacia afuera para no solapar con la mesa). Tooltip nativo
               via <title> alcanza en SVG; popover complejo queda para PASE
               Fase 2 cuando haya acciones sobre la reserva. -->
          <g
            v-if="reservationFor(table.id)"
            class="reservation-badge"
            :transform="`translate(${table.width / 2 - 4},${-table.height / 2 - 4})`"
          >
            <title>{{ reservationTooltip(reservationFor(table.id)!) }}</title>
            <rect
              x="-2"
              y="-12"
              width="42"
              height="18"
              rx="9"
              fill="#8e44ad"
              stroke="rgba(255,255,255,0.9)"
              stroke-width="1"
            />
            <text
              class="reservation-badge-text"
              x="19"
              y="-3"
              text-anchor="middle"
              dominant-baseline="middle"
            >🕐 {{ reservationTimeLabel(reservationFor(table.id)!.reserved_for) }}</text>
          </g>
        </g>
      </g>
    </svg>

    <div class="plano-viewport-hint" aria-hidden="true">
      <span>scroll: zoom · drag: pan</span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.plano-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  background: rgba(var(--v-theme-on-surface), 0.02);
  border-radius: 8px;
  overflow: hidden;
  user-select: none;
  touch-action: none;

  &.panning {
    cursor: grabbing;
  }
}

.plano-svg {
  width: 100%;
  height: 100%;
  display: block;
  cursor: grab;
}

.plano-table {
  cursor: pointer;

  &.editable {
    cursor: grab;
  }

  &.selected :is(ellipse, rect) {
    stroke: rgb(var(--v-theme-primary));
    stroke-width: 2.5;
  }

  &:hover :is(ellipse, rect) {
    filter: brightness(0.95);
  }
}

.table-name {
  font-size: 16px;
  font-weight: 700;
  fill: #1e272e;
  pointer-events: none;
}

.table-elapsed {
  font-size: 11px;
  font-weight: 500;
  fill: rgba(0, 0, 0, 0.7);
  pointer-events: none;
}

.table-total {
  font-size: 12px;
  font-weight: 700;
  fill: rgba(0, 0, 0, 0.85);
  pointer-events: none;
}

.reservation-badge {
  pointer-events: none;
}

.reservation-badge-text {
  font-size: 10px;
  font-weight: 700;
  fill: #fff;
  pointer-events: none;
}

.plano-viewport-hint {
  position: absolute;
  bottom: 8px;
  right: 12px;
  font-size: 11px;
  color: rgba(var(--v-theme-on-surface), 0.5);
  pointer-events: none;
}
</style>
