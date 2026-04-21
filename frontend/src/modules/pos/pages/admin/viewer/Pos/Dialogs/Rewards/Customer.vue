<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    customer: Record<string, any>
  }>()

  const { t } = useI18n()
</script>

<template>
  <v-container class="customer-section" fluid>
    <v-row align="center" justify="space-between">
      <v-col class="d-flex align-center mb-4 mb-md-0" cols="12" md="4">
        <v-avatar class="mr-2" size="60">
          <v-img alt="Tier" cover :src="customer.tier.icon?.url" />
        </v-avatar>
        <div>
          <h2 class="text-h6 font-weight-bold mb-1">{{ customer.name }}</h2>
          <div class="text-body-2 text-medium-emphasis">{{ customer.phone }}</div>
        </div>
      </v-col>

      <v-col class="text-center mb-4 mb-md-0" cols="12" md="4">
        <div class="text-caption text-medium-emphasis mb-1">
          {{ t('pos::pos_viewer.current_level') }}
        </div>
        <div class="text-h6 font-weight-bold mb-2">
          {{ customer.tier.name }}
        </div>
        <v-img
          alt="Tier"
          class="mx-auto"
          height="40"
          :src="customer.tier.icon?.url"
          width="40"
        />
      </v-col>

      <v-col class="text-md-right text-center" cols="12" md="4">
        <div class="text-caption text-medium-emphasis mb-1">
          {{ t('pos::pos_viewer.points_balance') }}
        </div>
        <div class="text-h5 font-weight-bold text-primary">
          {{ customer.points_balance }}
          <span class="text-subtitle-2 text-medium-emphasis">Pts</span>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.customer-section {
  background: linear-gradient(
    to right,
    rgba(var(--v-theme-primary), 0.08),
    rgba(var(--v-theme-surface-variant), 0.3)
  );
  border-radius: 15px;
}
</style>
