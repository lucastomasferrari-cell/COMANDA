<script lang="ts" setup>
  import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import { useToast } from 'vue-toastification'
  import { http } from '@/modules/core/api/http.ts'

  interface MetricCard {
    count?: number
    amount?: number
    ratio_percent?: number
    alert?: boolean
  }

  interface Summary {
    period: { from: string, to: string }
    cards: {
      sales_total: number
      voids: MetricCard
      discounts: MetricCard
      open_items: MetricCard
      pending_approvals: number
      payment_method_changes: number
      orders_reopened: number
    }
    top_voiders: Array<{ user_id: number, name: string, count: number }>
  }

  const { t } = useI18n()
  const toast = useToast()
  const router = useRouter()

  const rangePreset = ref<'today' | 'yesterday' | 'last_7' | 'last_30' | 'custom'>('today')
  const customFrom = ref<string>('')
  const customTo = ref<string>('')
  const summary = ref<Summary | null>(null)
  const loading = ref(false)
  let pollTimer: number | null = null

  const rangeDates = computed<{ from: string, to: string }>(() => {
    const now = new Date()
    const fmt = (d: Date) => d.toISOString().slice(0, 10)
    switch (rangePreset.value) {
      case 'yesterday': {
        const y = new Date(now)
        y.setDate(y.getDate() - 1)
        return { from: fmt(y), to: fmt(y) }
      }
      case 'last_7': {
        const f = new Date(now)
        f.setDate(f.getDate() - 6)
        return { from: fmt(f), to: fmt(now) }
      }
      case 'last_30': {
        const f = new Date(now)
        f.setDate(f.getDate() - 29)
        return { from: fmt(f), to: fmt(now) }
      }
      case 'custom':
        return { from: customFrom.value || fmt(now), to: customTo.value || fmt(now) }
      case 'today':
      default:
        return { from: fmt(now), to: fmt(now) }
    }
  })

  async function fetchSummary () {
    loading.value = true
    try {
      const res = await http.get('/v1/antifraud/summary', {
        params: rangeDates.value,
      })
      summary.value = res.data.body
    } catch {
      toast.error(t('core::errors.an_unexpected_error_occurred'))
    } finally {
      loading.value = false
    }
  }

  watch([rangePreset, customFrom, customTo], () => fetchSummary())

  onMounted(() => {
    fetchSummary()
    // Refresh cada 60s cuando la page esta abierta.
    pollTimer = window.setInterval(fetchSummary, 60_000)
  })
  onBeforeUnmount(() => { if (pollTimer !== null) window.clearInterval(pollTimer) })

  const goToPending = () => router.push({ name: 'admin.pending_approvals' })

  function cardColor (alert?: boolean): string {
    return alert ? 'error' : 'success'
  }
</script>

<template>
  <div class="antifraud-page">
    <!-- Range selector -->
    <VCard class="mb-4 pa-3" variant="outlined">
      <div class="d-flex flex-wrap align-center ga-3">
        <VBtnToggle v-model="rangePreset" color="primary" density="compact" mandatory variant="outlined">
          <VBtn value="today">{{ t('auditlog::dashboard.range.today') }}</VBtn>
          <VBtn value="yesterday">{{ t('auditlog::dashboard.range.yesterday') }}</VBtn>
          <VBtn value="last_7">{{ t('auditlog::dashboard.range.last_7') }}</VBtn>
          <VBtn value="last_30">{{ t('auditlog::dashboard.range.last_30') }}</VBtn>
          <VBtn value="custom">{{ t('auditlog::dashboard.range.custom') }}</VBtn>
        </VBtnToggle>
        <template v-if="rangePreset === 'custom'">
          <VTextField v-model="customFrom" density="compact" hide-details :label="t('auditlog::dashboard.range.from')" type="date" style="max-width: 170px;" />
          <VTextField v-model="customTo" density="compact" hide-details :label="t('auditlog::dashboard.range.to')" type="date" style="max-width: 170px;" />
        </template>
        <VSpacer />
        <VBtn :disabled="loading" :loading="loading" icon="tabler-refresh" size="small" variant="text" @click="fetchSummary" />
      </div>
    </VCard>

    <!-- Metric cards -->
    <VRow dense>
      <VCol cols="12" md="4">
        <VCard class="metric-card pa-3" :color="cardColor(summary?.cards?.voids?.alert)" variant="tonal">
          <div class="text-caption font-weight-medium">{{ t('auditlog::dashboard.cards.voids') }}</div>
          <div class="text-h4 font-weight-bold mt-1">{{ summary?.cards?.voids?.count ?? 0 }}</div>
          <div class="text-caption text-medium-emphasis">
            {{ (summary?.cards?.voids?.amount ?? 0).toFixed(2) }} —
            {{ summary?.cards?.voids?.ratio_percent ?? 0 }}%
          </div>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard class="metric-card pa-3" :color="cardColor(summary?.cards?.discounts?.alert)" variant="tonal">
          <div class="text-caption font-weight-medium">{{ t('auditlog::dashboard.cards.discounts') }}</div>
          <div class="text-h4 font-weight-bold mt-1">{{ summary?.cards?.discounts?.count ?? 0 }}</div>
          <div class="text-caption text-medium-emphasis">
            {{ (summary?.cards?.discounts?.amount ?? 0).toFixed(2) }} —
            {{ summary?.cards?.discounts?.ratio_percent ?? 0 }}%
          </div>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard class="metric-card pa-3" :color="cardColor(summary?.cards?.open_items?.alert)" variant="tonal">
          <div class="text-caption font-weight-medium">{{ t('auditlog::dashboard.cards.open_items') }}</div>
          <div class="text-h4 font-weight-bold mt-1">{{ summary?.cards?.open_items?.count ?? 0 }}</div>
          <div class="text-caption text-medium-emphasis">
            {{ (summary?.cards?.open_items?.amount ?? 0).toFixed(2) }}
          </div>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard
          class="metric-card pa-3 clickable"
          :color="(summary?.cards?.pending_approvals ?? 0) > 0 ? 'warning' : 'default'"
          variant="tonal"
          @click="goToPending"
        >
          <div class="text-caption font-weight-medium">{{ t('auditlog::dashboard.cards.pending_approvals') }}</div>
          <div class="text-h4 font-weight-bold mt-1">{{ summary?.cards?.pending_approvals ?? 0 }}</div>
          <div class="text-caption text-medium-emphasis">
            {{ t('auditlog::dashboard.cards.pending_cta') }}
          </div>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard class="metric-card pa-3" :color="(summary?.cards?.payment_method_changes ?? 0) > 0 ? 'warning' : 'default'" variant="tonal">
          <div class="text-caption font-weight-medium">{{ t('auditlog::dashboard.cards.payment_changes') }}</div>
          <div class="text-h4 font-weight-bold mt-1">{{ summary?.cards?.payment_method_changes ?? 0 }}</div>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard class="metric-card pa-3" :color="(summary?.cards?.orders_reopened ?? 0) > 0 ? 'warning' : 'default'" variant="tonal">
          <div class="text-caption font-weight-medium">{{ t('auditlog::dashboard.cards.orders_reopened') }}</div>
          <div class="text-h4 font-weight-bold mt-1">{{ summary?.cards?.orders_reopened ?? 0 }}</div>
        </VCard>
      </VCol>
    </VRow>

    <!-- Top voiders -->
    <VCard class="mt-4 pa-3" variant="outlined">
      <h3 class="text-subtitle-1 font-weight-medium mb-2">{{ t('auditlog::dashboard.top_voiders') }}</h3>
      <div v-if="!summary?.top_voiders?.length" class="text-caption text-medium-emphasis">
        {{ t('auditlog::dashboard.no_voids_yet') }}
      </div>
      <VTable v-else density="compact">
        <tbody>
          <tr v-for="v in summary.top_voiders" :key="v.user_id">
            <td>{{ v.name }}</td>
            <td class="text-right">
              <VChip color="error" size="small" variant="tonal">{{ v.count }}</VChip>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Sales reference -->
    <VCard class="mt-4 pa-3" variant="outlined">
      <div class="text-caption text-medium-emphasis">
        {{ t('auditlog::dashboard.sales_reference') }}:
        <strong>{{ (summary?.cards?.sales_total ?? 0).toFixed(2) }}</strong>
      </div>
    </VCard>
  </div>
</template>

<style lang="scss" scoped>
.metric-card {
  height: 100%;
  min-height: 100px;
}
.metric-card.clickable {
  cursor: pointer;
  transition: transform 0.15s ease;
}
.metric-card.clickable:hover {
  transform: translateY(-2px);
}
</style>
