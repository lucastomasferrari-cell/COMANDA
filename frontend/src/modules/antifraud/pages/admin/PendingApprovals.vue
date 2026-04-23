<script lang="ts" setup>
  import { computed, onMounted, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { http } from '@/modules/core/api/http.ts'

  interface PendingApproval {
    id: number
    user_id: number
    action: string
    related_model: string
    related_id: number
    details: Record<string, any> | null
    status: 'pending' | 'approved' | 'rejected' | 'expired'
    reviewed_by: number | null
    reviewed_at: string | null
    reviewer_notes: string | null
    expires_at: string
    created_at: string
    user?: { id: number, name: string }
    reviewer?: { id: number, name: string }
  }

  const { t } = useI18n()
  const toast = useToast()

  const statusFilter = ref<'pending' | 'approved' | 'rejected' | 'expired' | 'all'>('pending')
  const items = ref<PendingApproval[]>([])
  const loading = ref(false)
  const selected = ref<PendingApproval | null>(null)
  const drawerOpen = ref(false)
  const reviewNotes = ref<string>('')
  const reviewing = ref(false)

  async function fetchList () {
    loading.value = true
    try {
      const res = await http.get('/v1/pending-approvals', {
        params: { status: statusFilter.value },
      })
      items.value = res.data.body ?? []
    } catch {
      toast.error(t('core::errors.an_unexpected_error_occurred'))
    } finally {
      loading.value = false
    }
  }

  onMounted(fetchList)

  function open (row: PendingApproval) {
    selected.value = row
    reviewNotes.value = ''
    drawerOpen.value = true
  }

  async function approve () {
    if (!selected.value || reviewing.value) return
    reviewing.value = true
    try {
      await http.patch(`/v1/pending-approvals/${selected.value.id}/approve`, {
        notes: reviewNotes.value || null,
      })
      toast.success(t('auditlog::pending_approvals.approved_ok'))
      drawerOpen.value = false
      await fetchList()
    } catch (err: any) {
      toast.error(err?.response?.data?.message ?? t('core::errors.an_unexpected_error_occurred'))
    } finally {
      reviewing.value = false
    }
  }

  async function reject () {
    if (!selected.value || reviewing.value) return
    if (reviewNotes.value.trim().length < 10) {
      toast.warning(t('auditlog::pending_approvals.reject_notes_required'))
      return
    }
    reviewing.value = true
    try {
      await http.patch(`/v1/pending-approvals/${selected.value.id}/reject`, {
        notes: reviewNotes.value.trim(),
      })
      toast.success(t('auditlog::pending_approvals.rejected_ok'))
      drawerOpen.value = false
      await fetchList()
    } catch (err: any) {
      toast.error(err?.response?.data?.message ?? t('core::errors.an_unexpected_error_occurred'))
    } finally {
      reviewing.value = false
    }
  }

  function statusChip (status: string): { color: string, label: string } {
    switch (status) {
      case 'pending': return { color: 'warning', label: t('auditlog::pending_approvals.status.pending') }
      case 'approved': return { color: 'success', label: t('auditlog::pending_approvals.status.approved') }
      case 'rejected': return { color: 'error', label: t('auditlog::pending_approvals.status.rejected') }
      case 'expired': return { color: 'grey', label: t('auditlog::pending_approvals.status.expired') }
      default: return { color: 'default', label: status }
    }
  }
</script>

<template>
  <div>
    <VCard class="mb-4 pa-3" variant="outlined">
      <div class="d-flex align-center ga-3">
        <VBtnToggle
          v-model="statusFilter"
          color="primary"
          density="compact"
          mandatory
          variant="outlined"
          @update:model-value="fetchList"
        >
          <VBtn value="pending">{{ t('auditlog::pending_approvals.status.pending') }}</VBtn>
          <VBtn value="approved">{{ t('auditlog::pending_approvals.status.approved') }}</VBtn>
          <VBtn value="rejected">{{ t('auditlog::pending_approvals.status.rejected') }}</VBtn>
          <VBtn value="expired">{{ t('auditlog::pending_approvals.status.expired') }}</VBtn>
          <VBtn value="all">{{ t('auditlog::pending_approvals.status.all') }}</VBtn>
        </VBtnToggle>
        <VSpacer />
        <VBtn :disabled="loading" :loading="loading" icon="tabler-refresh" size="small" variant="text" @click="fetchList" />
      </div>
    </VCard>

    <VCard>
      <VTable>
        <thead>
          <tr>
            <th>{{ t('auditlog::pending_approvals.columns.date') }}</th>
            <th>{{ t('auditlog::pending_approvals.columns.user') }}</th>
            <th>{{ t('auditlog::pending_approvals.columns.action') }}</th>
            <th>{{ t('auditlog::pending_approvals.columns.entity') }}</th>
            <th>{{ t('auditlog::pending_approvals.columns.status') }}</th>
            <th />
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in items" :key="row.id" class="clickable" @click="open(row)">
            <td class="text-caption">{{ row.created_at }}</td>
            <td>{{ row.user?.name ?? `#${row.user_id}` }}</td>
            <td>{{ row.action }}</td>
            <td class="text-caption">{{ row.related_model.split('\\').pop() }} #{{ row.related_id }}</td>
            <td>
              <VChip :color="statusChip(row.status).color" size="small" variant="tonal">
                {{ statusChip(row.status).label }}
              </VChip>
            </td>
            <td><VIcon icon="tabler-chevron-right" size="16" /></td>
          </tr>
          <tr v-if="!items.length && !loading">
            <td colspan="6" class="text-center text-medium-emphasis py-4">
              {{ t('auditlog::pending_approvals.empty') }}
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VNavigationDrawer v-model="drawerOpen" location="right" temporary :width="480">
      <VCard v-if="selected" class="h-100 d-flex flex-column">
        <VCardText class="flex-grow-1 overflow-y-auto">
          <div class="d-flex align-center justify-space-between mb-3">
            <h3 class="text-h6">
              {{ t('auditlog::pending_approvals.detail_title') }}
            </h3>
            <VBtn density="compact" icon="tabler-x" variant="text" @click="drawerOpen = false" />
          </div>

          <div class="mb-3">
            <VChip :color="statusChip(selected.status).color" size="small" variant="tonal">
              {{ statusChip(selected.status).label }}
            </VChip>
          </div>

          <dl class="detail-grid">
            <dt>{{ t('auditlog::pending_approvals.columns.action') }}</dt>
            <dd>{{ selected.action }}</dd>
            <dt>{{ t('auditlog::pending_approvals.columns.user') }}</dt>
            <dd>{{ selected.user?.name ?? `#${selected.user_id}` }}</dd>
            <dt>{{ t('auditlog::pending_approvals.columns.entity') }}</dt>
            <dd>{{ selected.related_model.split('\\').pop() }} #{{ selected.related_id }}</dd>
            <dt>{{ t('auditlog::pending_approvals.columns.date') }}</dt>
            <dd>{{ selected.created_at }}</dd>
            <dt>{{ t('auditlog::pending_approvals.columns.expires') }}</dt>
            <dd>{{ selected.expires_at }}</dd>
          </dl>

          <VDivider class="my-3" />

          <h4 class="text-subtitle-2 mb-1">{{ t('auditlog::pending_approvals.details') }}</h4>
          <pre class="details-pre">{{ JSON.stringify(selected.details, null, 2) }}</pre>

          <template v-if="selected.status === 'pending'">
            <VDivider class="my-3" />
            <VTextarea
              v-model="reviewNotes"
              :label="t('auditlog::pending_approvals.review_notes')"
              :hint="t('auditlog::pending_approvals.reject_notes_required')"
              persistent-hint
              rows="3"
              variant="outlined"
            />
          </template>

          <template v-if="selected.reviewer_notes">
            <VDivider class="my-3" />
            <h4 class="text-subtitle-2 mb-1">{{ t('auditlog::pending_approvals.reviewer_notes') }}</h4>
            <p class="text-body-2">{{ selected.reviewer_notes }}</p>
            <p class="text-caption text-medium-emphasis mt-1">
              {{ selected.reviewer?.name ?? `#${selected.reviewed_by}` }} — {{ selected.reviewed_at }}
            </p>
          </template>
        </VCardText>

        <div v-if="selected.status === 'pending'" class="pa-3 border-top d-flex ga-2">
          <VBtn block color="error" :loading="reviewing" variant="tonal" @click="reject">
            {{ t('auditlog::pending_approvals.reject') }}
          </VBtn>
          <VBtn block color="success" :loading="reviewing" @click="approve">
            {{ t('auditlog::pending_approvals.approve') }}
          </VBtn>
        </div>
      </VCard>
    </VNavigationDrawer>
  </div>
</template>

<style lang="scss" scoped>
.clickable { cursor: pointer; }
.clickable:hover { background: rgba(var(--v-theme-on-surface), 0.03); }
.detail-grid {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0.5rem 1rem;
  font-size: 0.875rem;
  dt { font-weight: 600; color: rgba(var(--v-theme-on-surface), 0.7); }
}
.details-pre {
  background: rgba(var(--v-theme-on-surface), 0.04);
  padding: 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  max-height: 200px;
  overflow: auto;
}
</style>
