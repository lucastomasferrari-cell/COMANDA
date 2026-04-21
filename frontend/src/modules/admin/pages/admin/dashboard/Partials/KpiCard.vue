<script lang="ts" setup>

  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const { can } = useAuth()

  withDefaults(defineProps<{
    title: string
    value: string | number | undefined
    loading?: boolean
    permission?: string
    icon?: string
    color?: string
  }>(), {
    color: 'primary',
    loading: false,
  })

</script>

<template>
  <VCol
    v-if="!permission || can(permission)"
    cols="12"
    lg="3"
    md="4"
    sm="6"
  >
    <VCard
      class="dashboard-kpi"
      height="112"
    >
      <div class="dashboard-kpi__content">
        <div class="dashboard-kpi__title">{{ title }}</div>
        <div class="dashboard-kpi__value">
          <VProgressCircular
            v-if="loading"
            color="primary"
            indeterminate
            size="23"
            width="2.5"
          />
          <span v-else>{{ value }}</span>
        </div>
      </div>
      <VAvatar
        v-if="icon"
        class="dashboard-kpi__icon"
        :color="color"
        rounded="lg"
        size="52"
        variant="tonal"
      >
        <VIcon :icon="icon" size="28" />
      </VAvatar>
    </VCard>
  </VCol>
</template>

<style lang="scss" scoped>
.dashboard-kpi {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px;
  border: 1px solid rgba(var(--v-theme-on-surface), .08);
  border-radius: 18px;
  box-shadow: 0 16px 34px rgba(15, 23, 42, .05);
  background: linear-gradient(180deg, rgba(var(--v-theme-surface), 1) 0%, rgba(var(--v-theme-surface), .96) 100%);
}

.dashboard-kpi__content {
  min-width: 0;
}

.dashboard-kpi__title {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: rgba(var(--v-theme-on-surface), .56);
}

.dashboard-kpi__value {
  margin-top: 10px;
  font-size: 1.7rem;
  font-weight: 800;
  line-height: 1;
  color: rgba(var(--v-theme-on-surface), .96);
}

.dashboard-kpi__icon {
  border: 1px solid rgba(var(--v-theme-on-surface), .08);
}
</style>
