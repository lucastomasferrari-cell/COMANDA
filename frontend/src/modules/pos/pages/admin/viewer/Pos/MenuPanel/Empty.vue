<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()
</script>

<template>
  <div class="empty-menu-state">
    <div class="icon-wrapper">
      <VIcon icon="tabler-chef-hat-off" size="32" />
    </div>

    <h4 class="title">{{ t("pos::pos_viewer.menu_no_available.title") }}</h4>

    <p class="subtitle">
      {{ t("pos::pos_viewer.menu_no_available.message") }}
    </p>
  </div>
</template>

<style lang="scss" scoped>
/* Usa theme vars de Vuetify para que respete light/dark mode del appStore.
   Antes estaba hardcoded con grises que quedaban ilegibles en dark mode. */
.empty-menu-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  text-align: center;
  color: rgba(var(--v-theme-on-surface), 0.7);

  .icon-wrapper {
    background: rgba(var(--v-theme-on-surface), 0.05);
    border-radius: 50%;
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;

    :deep(.v-icon) {
      color: rgba(var(--v-theme-on-surface), 0.45);
    }
  }

  .title {
    font-weight: bold;
    margin-bottom: 0.4rem;
    color: rgb(var(--v-theme-on-surface));
  }

  .subtitle {
    font-size: 0.95rem;
    color: rgba(var(--v-theme-on-surface), 0.7);
  }
}
</style>
