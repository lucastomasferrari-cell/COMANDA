<script lang="ts" setup>
  import { watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useDashboard } from '@/modules/admin/composables/dashboard.ts'

  defineProps<{
    filters: Record<string, any>[]
  }>()

  const { t } = useI18n()
  const { bestPerformingBranches } = useDashboard()

  const loading = ref(false)
  const filter = ref('all_time')
  const branches = ref<Record<string, any>[]>([])

  watch(filter, () => loadData())

  onBeforeMount(() => loadData())

  async function loadData () {
    try {
      loading.value = true
      const response = await bestPerformingBranches(filter.value)
      branches.value = response.data.body
    } catch {} finally {
      loading.value = false
    }
  }

  const colors = ['#1d4ed8', '#0f766e', '#f57c00', '#e11d48', '#7c3aed']

</script>

<template>
  <VCard class="dashboard-panel" height="380">
    <VCardTitle class="dashboard-panel__header">
      <div class="dashboard-panel__title">
        <VIcon class="dashboard-panel__icon" icon="tabler-building-store" size="22" />
        {{ t('dashboard::dashboards.best_performing_branches') }}
      </div>
      <VSelect
        v-model="filter"
        class="dashboard-panel__select"
        density="compact"
        :disabled="loading"
        item-title="name"
        item-value="id"
        :items="filters"
        style="max-width: 140px"
        variant="outlined"
      />
    </VCardTitle>
    <VCardText>
      <div v-if="loading || branches.length === 0" class="dashboard-panel__state">
        <VProgressCircular
          v-if="loading"
          color="primary"
          indeterminate
          size="40"
        />
        <span v-else class="text-body-1">
          {{ t('dashboard::dashboards.no_data_available') }}
        </span>
      </div>
      <div v-if="!loading && branches.length>0" class="dashboard-list">
        <VRow dense>
          <VCol
            v-for="(branch, index) in branches"
            :key="branch.branch_id"
            cols="12"
          >
            <VCard class="dashboard-list__item">
              <div class="d-flex align-center ga-3">
                <VAvatar
                  :color="colors[index] ?? 'grey'"
                  rounded
                  size="40"
                  variant="tonal"
                >
                  <span class="font-weight-bold">
                    {{ branch.branch_name.charAt(0) }}
                  </span>
                </VAvatar>

                <div>
                  <div class="text-body-1 font-weight-bold">
                    {{ branch.branch_name }}
                  </div>
                  <div class="text-caption text-muted">
                    {{ t('dashboard::dashboards.total_orders') }}: {{ branch.total_orders }}
                  </div>
                </div>
              </div>
              <div class="text-end font-weight-bold">
                {{ branch.total_sales.formatted }}
              </div>
            </VCard>
          </VCol>
        </VRow>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.dashboard-panel {
  border: 1px solid rgba(var(--v-theme-on-surface), .08);
  border-radius: 18px;
  box-shadow: 0 18px 36px rgba(15, 23, 42, .05);
}

.dashboard-panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding-block-end: 14px;
  margin-block-end: 8px;
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), .08);
  font-size: 1.1rem;
  font-weight: 800;
}

.dashboard-panel__title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.dashboard-panel__icon {
  color: #1d4ed8;
}

.dashboard-panel__state,
.dashboard-list {
  height: 300px;
}

.dashboard-panel__state {
  display: flex;
  align-items: center;
  justify-content: center;
}

.dashboard-list {
  overflow-x: hidden;
  overflow-y: auto;
}

.dashboard-list__item {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 14px;
  border: 1px dashed rgba(var(--v-theme-on-surface), .12);
  border-radius: 16px;
  box-shadow: none;
}
</style>
