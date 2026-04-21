<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useDashboard } from '@/modules/admin/composables/dashboard.ts'

  const { t } = useI18n()
  const { lowStockAlerts } = useDashboard()

  const loading = ref(false)
  const alerts = ref<Record<string, any>[]>([])

  onBeforeMount(() => loadData())

  async function loadData () {
    try {
      loading.value = true
      const response = await lowStockAlerts()
      alerts.value = response.data.body
    } catch {} finally {
      loading.value = false
    }
  }

</script>

<template>
  <VCard class="dashboard-panel" height="380">
    <VCardTitle class="dashboard-panel__header">
      <div class="dashboard-panel__title">
        <VIcon class="dashboard-panel__icon" icon="tabler-alert-triangle" size="22" />
        {{ t('dashboard::dashboards.low_stock_alerts') }}
      </div>
    </VCardTitle>
    <VCardText>
      <div v-if="loading || alerts.length === 0" class="dashboard-panel__state">
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
      <div v-if="!loading && alerts.length>0" class="dashboard-list">
        <VRow dense>
          <VCol
            v-for="(alert) in alerts"
            :key="alert.id"
            cols="12"
          >
            <VCard class="dashboard-list__item">
              <div class="d-flex align-center gap-3">
                <VAvatar
                  :color="alert.current_stock<=0?'error':'warning'"
                  rounded
                  size="40"
                  variant="tonal"
                >
                  <span class="font-weight-bold">
                    {{ alert.name.charAt(0) }}
                  </span>
                </VAvatar>
                <div>
                  <div class="text-body-1 font-weight-bold">
                    {{ alert.name }}
                  </div>
                  <div class="text-caption text-muted">
                    {{ t('dashboard::dashboards.alert_quantity') }}: {{ alert.alert_quantity }}
                    {{ alert.symbol }}
                  </div>
                </div>
              </div>
              <div class="text-end">
                <VChip size="small" variant="tonal">
                  {{ alert.current_stock }} {{ alert.symbol }}
                </VChip>
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
  color: #e11d48;
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
