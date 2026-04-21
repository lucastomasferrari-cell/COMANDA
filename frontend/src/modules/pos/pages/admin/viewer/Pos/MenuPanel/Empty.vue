<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()
</script>

<template>
  <div class="empty-menu-state">
    <div class="icon-wrapper">
      <i class="tabler-chef-hat-off" />
    </div>

    <h4 class="title">{{ t("pos::pos_viewer.menu_no_available.title") }}</h4>

    <p class="subtitle">
      {{ t("pos::pos_viewer.menu_no_available.message") }}
    </p>
  </div>
</template>

<style lang="scss" scoped>
.empty-menu-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  text-align: center;
  color: #606266;

  .icon-wrapper {
    background: #f0f2f5;
    border-radius: 50%;
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;

    i {
      font-size: 32px;
      color: #909399;
    }
  }

  .title {
    font-weight: bold;
    margin-bottom: 0.4rem;
    color: #303133;
  }

  .subtitle {
    font-size: 0.95rem;
    color: #606266;
  }
}
</style>
