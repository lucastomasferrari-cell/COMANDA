<script lang="ts" setup>
  import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { http } from '@/modules/core/api/http.ts'

  const { t } = useI18n()
  const router = useRouter()
  const { can } = useAuth()

  const alertsCount = ref<number>(0)
  const loading = ref(false)
  let pollTimer: number | null = null
  let lastCount = 0
  const pulse = ref(false)

  // Flag para animar cuando crece el número.
  function bumpPulse () {
    pulse.value = true
    window.setTimeout(() => { pulse.value = false }, 1200)
  }

  async function fetchCount () {
    if (!can('admin.audit_logs.index')) return
    loading.value = true
    try {
      const res = await http.get('/v1/antifraud/summary', {
        params: {
          from: new Date().toISOString().slice(0, 10),
          to: new Date().toISOString().slice(0, 10),
        },
      })
      const cards = res.data.body?.cards ?? {}
      let alerts = cards.pending_approvals ?? 0
      if (cards.voids?.alert) alerts += 1
      if (cards.discounts?.alert) alerts += 1
      if (cards.open_items?.alert) alerts += 1

      if (alerts > lastCount) bumpPulse()
      lastCount = alerts
      alertsCount.value = alerts
    } catch {
      // silencioso
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    fetchCount()
    pollTimer = window.setInterval(fetchCount, 60_000)
  })
  onBeforeUnmount(() => { if (pollTimer !== null) window.clearInterval(pollTimer) })

  const open = () => router.push({ name: 'admin.antifraud' })
  const badgeColor = computed(() => alertsCount.value > 0 ? 'error' : 'success')
  const showBadge = computed(() => alertsCount.value > 0)
</script>

<template>
  <VTooltip location="bottom" :text="t('admin::navbar.antifraud_alerts')">
    <template #activator="{ props: activator }">
      <VBtn
        v-bind="activator"
        class="navbar-antifraud"
        :class="{ pulse }"
        color="default"
        icon
        variant="text"
        @click="open"
      >
        <VBadge
          v-if="showBadge"
          :color="badgeColor"
          :content="alertsCount > 99 ? '99+' : alertsCount"
          offset-x="2"
          offset-y="2"
        >
          <VIcon icon="tabler-shield-lock" size="22" />
        </VBadge>
        <VIcon v-else icon="tabler-shield-check" size="22" />
      </VBtn>
    </template>
  </VTooltip>
</template>

<style lang="scss" scoped>
.navbar-antifraud.pulse {
  animation: antifraud-pulse 1.2s ease-in-out;
}

@keyframes antifraud-pulse {
  0%   { transform: scale(1); }
  25%  { transform: scale(1.18); }
  50%  { transform: scale(0.92); }
  75%  { transform: scale(1.08); }
  100% { transform: scale(1); }
}
</style>
