<script lang="ts" setup>
  import type { PlanoTable } from './SalonPlanoVisual.vue'
  import { computed, onMounted, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import { useToast } from 'vue-toastification'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useTable } from '@/modules/seatingPlan/composables/table.ts'
  import SalonPlanoVisual from './SalonPlanoVisual.vue'

  const { t } = useI18n()
  const { can } = useAuth()
  const { getTableViewer, updatePositions: apiUpdatePositions } = useTable()
  const toast = useToast()
  const router = useRouter()

  const tables = ref<PlanoTable[]>([])
  const loading = ref(false)
  const editing = ref(false)
  const snap = ref(true)
  const saving = ref(false)
  const selectedId = ref<number | null>(null)
  const dirtyPositions = ref<Record<number, { position_x: number, position_y: number }>>({})

  // right-click menu
  const contextMenu = ref<{ visible: boolean, x: number, y: number, tableId: number | null }>({
    visible: false, x: 0, y: 0, tableId: null,
  })

  const canEdit = computed(() => can('admin.tables.edit'))
  const canCreate = computed(() => can('admin.tables.create'))
  const hasDirty = computed(() => Object.keys(dirtyPositions.value).length > 0)
  const selectedTable = computed(() => tables.value.find(t => t.id === selectedId.value) ?? null)

  async function load () {
    loading.value = true
    try {
      const res = await getTableViewer(null, {})
      const items = (res.data.body.tables ?? []) as any[]
      tables.value = items.map(t => ({
        id: t.id,
        name: typeof t.name === 'string' ? t.name : t.name?.value ?? '',
        shape: t.shape ?? 'circle',
        position_x: t.position_x,
        position_y: t.position_y,
        width: Number(t.width) || 80,
        height: Number(t.height) || 80,
        rotation: Number(t.rotation) || 0,
        status: t.status ?? null,
        capacity: t.capacity,
        active_order: t.active_order ?? null,
      }))
    } catch {
      toast.error(t('core::errors.an_unexpected_error_occurred'))
    } finally {
      loading.value = false
    }
  }

  onMounted(load)

  function onMove (payload: { id: number, position_x: number, position_y: number }) {
    const tbl = tables.value.find(t => t.id === payload.id)
    if (!tbl) return
    tbl.position_x = payload.position_x
    tbl.position_y = payload.position_y
    dirtyPositions.value[payload.id] = { position_x: payload.position_x, position_y: payload.position_y }
  }

  async function save () {
    if (!hasDirty.value) return
    saving.value = true
    try {
      const positions = tables.value
        .filter(tbl => tbl.id in dirtyPositions.value)
        .map(tbl => ({
          id: tbl.id,
          position_x: Number(tbl.position_x),
          position_y: Number(tbl.position_y),
          width: Number(tbl.width),
          height: Number(tbl.height),
          rotation: Number(tbl.rotation) || 0,
        }))
      const res = await apiUpdatePositions(positions)
      toast.success(res.data?.message ?? t('seatingplan::plano.toasts.saved'))
      dirtyPositions.value = {}
    } catch (err: any) {
      toast.error(err?.response?.data?.message ?? t('core::errors.an_unexpected_error_occurred'))
    } finally {
      saving.value = false
    }
  }

  async function discard () {
    dirtyPositions.value = {}
    await load()
  }

  function openEditing () {
    editing.value = true
    toast.info(t('seatingplan::plano.toasts.editing_on'))
  }

  function stopEditing () {
    if (hasDirty.value) {
      toast.warning(t('seatingplan::plano.toasts.save_before_exit'))
      return
    }
    editing.value = false
    selectedId.value = null
  }

  function onSelect (id: number) {
    selectedId.value = id
  }

  function onContextMenu (payload: { table: PlanoTable, x: number, y: number }) {
    if (!editing.value) return
    selectedId.value = payload.table.id
    contextMenu.value = { visible: true, x: payload.x, y: payload.y, tableId: payload.table.id }
  }

  function closeContextMenu () {
    contextMenu.value.visible = false
  }

  function applyShape (shape: 'circle' | 'square' | 'rectangle') {
    if (selectedId.value == null) return
    const tbl = tables.value.find(t => t.id === selectedId.value)
    if (!tbl) return
    tbl.shape = shape
    if (shape === 'square' || shape === 'circle') {
      const side = Math.max(tbl.width, tbl.height)
      tbl.width = side
      tbl.height = side
    } else if (shape === 'rectangle' && tbl.width === tbl.height) {
      tbl.width = tbl.height * 1.4
    }
    markDirty(tbl)
    closeContextMenu()
  }

  function applySize (delta: number) {
    if (selectedId.value == null) return
    const tbl = tables.value.find(t => t.id === selectedId.value)
    if (!tbl) return
    tbl.width = Math.max(30, Math.min(300, tbl.width + delta))
    tbl.height = Math.max(30, Math.min(300, tbl.height + (tbl.shape === 'rectangle' ? delta * 0.7 : delta)))
    markDirty(tbl)
  }

  function applyRotate () {
    if (selectedId.value == null) return
    const tbl = tables.value.find(t => t.id === selectedId.value)
    if (!tbl) return
    tbl.rotation = (tbl.rotation + 90) % 360
    markDirty(tbl)
    closeContextMenu()
  }

  function markDirty (tbl: PlanoTable) {
    dirtyPositions.value[tbl.id] = {
      position_x: Number(tbl.position_x ?? 0),
      position_y: Number(tbl.position_y ?? 0),
    }
  }

  function openCreateTable () {
    router.push({ name: 'admin.tables.create' })
  }

  function goToEditTable () {
    if (selectedId.value == null) return
    router.push({ name: 'admin.tables.edit', params: { id: selectedId.value } })
  }
</script>

<template>
  <VCard class="plano-editor-card" :loading="loading || saving">
    <VCardText class="pa-0">
      <!-- toolbar -->
      <div class="plano-toolbar d-flex align-center ga-2 px-4 py-2">
        <h4 class="text-subtitle-1 font-weight-medium flex-grow-1">
          {{ t('seatingplan::plano.title') }}
          <span class="text-caption text-medium-emphasis">
            ({{ tables.length }})
          </span>
        </h4>
        <template v-if="!editing">
          <VBtn
            v-if="canEdit"
            color="primary"
            prepend-icon="tabler-edit"
            size="small"
            variant="tonal"
            @click="openEditing"
          >
            {{ t('seatingplan::plano.actions.edit') }}
          </VBtn>
        </template>
        <template v-else>
          <VBtn
            v-if="canCreate"
            color="default"
            prepend-icon="tabler-plus"
            size="small"
            variant="tonal"
            @click="openCreateTable"
          >
            {{ t('seatingplan::plano.actions.add_table') }}
          </VBtn>
          <VSwitch
            v-model="snap"
            color="primary"
            density="compact"
            hide-details
            :label="t('seatingplan::plano.actions.snap')"
          />
          <VBtn
            color="default"
            :disabled="saving"
            size="small"
            variant="tonal"
            @click="discard"
          >
            {{ t('seatingplan::plano.actions.discard') }}
          </VBtn>
          <VBtn
            color="primary"
            :disabled="!hasDirty||saving"
            :loading="saving"
            prepend-icon="tabler-device-floppy"
            size="small"
            @click="save"
          >
            {{ t('seatingplan::plano.actions.save') }}
          </VBtn>
          <VBtn
            color="default"
            :disabled="hasDirty"
            size="small"
            variant="text"
            @click="stopEditing"
          >
            {{ t('seatingplan::plano.actions.exit') }}
          </VBtn>
        </template>
      </div>

      <!-- canvas -->
      <div class="plano-stage">
        <div v-if="!loading && tables.length === 0" class="empty-plano">
          <VIcon icon="tabler-layout-grid" size="56" color="primary" />
          <p class="text-subtitle-2 mt-3">{{ t('seatingplan::plano.empty.title') }}</p>
          <p class="text-caption text-medium-emphasis mb-3">
            {{ t('seatingplan::plano.empty.description') }}
          </p>
          <VBtn v-if="canCreate" color="primary" prepend-icon="tabler-plus" @click="openCreateTable">
            {{ t('seatingplan::plano.empty.cta') }}
          </VBtn>
        </div>

        <SalonPlanoVisual
          v-else
          :tables="tables"
          :editable="editing"
          :selected-id="selectedId"
          :snap="snap"
          @move="onMove"
          @select="onSelect"
          @context-menu="onContextMenu"
        />
      </div>

      <!-- selected action bar (editing only, bottom fixed) -->
      <div
        v-if="editing && selectedTable"
        class="plano-selected-bar d-flex align-center ga-2 px-4 py-2"
      >
        <VIcon icon="tabler-brand-airtable" />
        <span class="font-weight-medium">{{ selectedTable.name }}</span>
        <VDivider class="mx-2" vertical />
        <VBtnGroup density="compact" divided variant="tonal">
          <VBtn
            :title="t('seatingplan::plano.shape.circle')"
            icon="tabler-circle"
            @click="applyShape('circle')"
          />
          <VBtn
            :title="t('seatingplan::plano.shape.square')"
            icon="tabler-square"
            @click="applyShape('square')"
          />
          <VBtn
            :title="t('seatingplan::plano.shape.rectangle')"
            icon="tabler-rectangle"
            @click="applyShape('rectangle')"
          />
        </VBtnGroup>
        <VBtnGroup density="compact" divided variant="tonal">
          <VBtn icon="tabler-arrows-diagonal-minimize-2" @click="applySize(-10)" />
          <VBtn icon="tabler-arrows-diagonal" @click="applySize(10)" />
        </VBtnGroup>
        <VBtn icon="tabler-rotate-clockwise" size="small" variant="tonal" @click="applyRotate" />
        <VSpacer />
        <VBtn
          color="primary"
          prepend-icon="tabler-pencil"
          size="small"
          variant="text"
          @click="goToEditTable"
        >
          {{ t('seatingplan::plano.actions.full_edit') }}
        </VBtn>
      </div>
    </VCardText>

    <!-- context menu -->
    <VMenu
      v-model="contextMenu.visible"
      :target="[contextMenu.x, contextMenu.y]"
      :close-on-content-click="false"
    >
      <VList density="compact">
        <VListItem @click="applyShape('circle')">
          <template #prepend><VIcon icon="tabler-circle" /></template>
          <VListItemTitle>{{ t('seatingplan::plano.shape.circle') }}</VListItemTitle>
        </VListItem>
        <VListItem @click="applyShape('square')">
          <template #prepend><VIcon icon="tabler-square" /></template>
          <VListItemTitle>{{ t('seatingplan::plano.shape.square') }}</VListItemTitle>
        </VListItem>
        <VListItem @click="applyShape('rectangle')">
          <template #prepend><VIcon icon="tabler-rectangle" /></template>
          <VListItemTitle>{{ t('seatingplan::plano.shape.rectangle') }}</VListItemTitle>
        </VListItem>
        <VDivider />
        <VListItem @click="applyRotate">
          <template #prepend><VIcon icon="tabler-rotate-clockwise" /></template>
          <VListItemTitle>{{ t('seatingplan::plano.actions.rotate') }}</VListItemTitle>
        </VListItem>
        <VListItem @click="goToEditTable">
          <template #prepend><VIcon icon="tabler-pencil" /></template>
          <VListItemTitle>{{ t('seatingplan::plano.actions.full_edit') }}</VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>
  </VCard>
</template>

<style lang="scss" scoped>
.plano-editor-card {
  border-radius: 12px;
  overflow: hidden;
}

.plano-toolbar {
  border-bottom: thin solid rgba(var(--v-theme-on-surface), 0.1);
  background: rgba(var(--v-theme-on-surface), 0.02);
}

.plano-stage {
  height: 600px;
  position: relative;
}

.empty-plano {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 2rem;
}

.plano-selected-bar {
  border-top: thin solid rgba(var(--v-theme-on-surface), 0.1);
  background: rgba(var(--v-theme-primary), 0.04);
}
</style>
