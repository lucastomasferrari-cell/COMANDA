<script lang="ts" setup>
  import type { PlanoTable } from '@/modules/seatingPlan/components/SalonPlanoVisual.vue'
  import { onBeforeUnmount, onMounted, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import SalonPlanoVisual from '@/modules/seatingPlan/components/SalonPlanoVisual.vue'
  import { useTable } from '@/modules/seatingPlan/composables/table.ts'

  const props = defineProps<{
    branchId: number | null
  }>()

  const emit = defineEmits<{
    (e: 'pick-free', table: PlanoTable): void
    (e: 'pick-occupied', table: PlanoTable): void
  }>()

  const { t } = useI18n()
  const { getTableViewer } = useTable()
  const toast = useToast()

  const tables = ref<PlanoTable[]>([])
  const loading = ref(false)
  let pollTimer: number | null = null

  async function fetchTables () {
    if (props.branchId == null) return
    loading.value = true
    try {
      const res = await getTableViewer(props.branchId, {})
      tables.value = (res.data.body.tables ?? []).map((t: any) => {
        const activeOrder = t.active_order ?? null
        // Override sintético de status: bill_requested y paused pisan el
        // status real para que el plano los pinte rojo/gris respectivamente.
        let statusOverride = t.status ?? null
        if (activeOrder?.bill_requested_at && statusOverride) {
          statusOverride = { ...statusOverride, id: 'bill_requested' }
        } else if (activeOrder?.paused_at && statusOverride) {
          statusOverride = { ...statusOverride, id: 'paused' }
        }
        return {
          id: t.id,
          name: typeof t.name === 'string' ? t.name : t.name?.value ?? '',
          shape: t.shape ?? 'circle',
          position_x: t.position_x,
          position_y: t.position_y,
          width: Number(t.width) || 80,
          height: Number(t.height) || 80,
          rotation: Number(t.rotation) || 0,
          status: statusOverride,
          capacity: t.capacity,
          active_order: activeOrder,
        }
      })
    } catch {
      // silencioso: el polling no interrumpe al usuario
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    fetchTables()
    pollTimer = window.setInterval(() => fetchTables(), 30_000)
  })

  onBeforeUnmount(() => {
    if (pollTimer !== null) window.clearInterval(pollTimer)
  })

  function onClickFree (table: PlanoTable) {
    emit('pick-free', table)
  }

  function onClickOccupied (table: PlanoTable) {
    emit('pick-occupied', table)
  }

  function onContextMenu (_payload: { table: PlanoTable, x: number, y: number }) {
    // Acciones avanzadas (cambiar mesa / unir / liberar) viven en el
    // TableViewerDrawer. Dejamos el hook listo para integracion futura.
    toast.info(t('pos::pos_viewer.plano.context_placeholder'))
  }
</script>

<template>
  <div class="pos-plano-wrapper">
    <SalonPlanoVisual
      v-if="tables.length > 0"
      :tables="tables"
      @click-free="onClickFree"
      @click-occupied="onClickOccupied"
      @context-menu="onContextMenu"
    />
    <div v-else-if="loading" class="d-flex align-center justify-center h-100">
      <VProgressCircular color="primary" indeterminate size="40" />
    </div>
    <div v-else class="d-flex flex-column align-center justify-center text-center h-100 px-4">
      <VIcon class="mb-3" color="primary" icon="tabler-layout-grid" size="48" />
      <p class="text-subtitle-2 mb-1">
        {{ t('pos::pos_viewer.plano.empty_title') }}
      </p>
      <p class="text-caption text-medium-emphasis">
        {{ t('pos::pos_viewer.plano.empty_description') }}
      </p>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.pos-plano-wrapper {
  width: 100%;
  height: 100%;
  min-height: 400px;
}
</style>
