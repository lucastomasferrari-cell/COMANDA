<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()
</script>

<template>
  <v-container class="empty-state d-flex flex-column align-center justify-center text-center py-16">
    <div class="empty-icon mb-6 d-flex align-center justify-center">
      <v-icon
        color="primary"
        icon="tabler-gift-off"
        size="100"
      />
    </div>

    <h2 class="text-h6 font-weight-bold mb-2">
      {{ t('pos::pos_viewer.empty_no_gifts_available.title') }}
    </h2>

    <p class="text-body-2 text-medium-emphasis mb-6">
      {{ t('pos::pos_viewer.empty_no_gifts_available.message') }}
    </p>
  </v-container>
</template>

<style scoped>
.empty-state {
  min-height: 60vh;
  color: rgba(var(--v-theme-on-surface), 0.8);
}

.empty-icon {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-primary), 0.1),
    rgba(var(--v-theme-surface-variant), 0.2)
  );
  backdrop-filter: blur(8px);
}
</style>
