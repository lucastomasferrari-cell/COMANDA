<script lang="ts" setup>

  import type { NavSectionTitle } from '@/modules/core/contracts/Theme.ts'

  defineProps<{
    item: NavSectionTitle
  }>()
</script>

<template>
  <li class="nav-section-title">
    <div class="title-wrapper">
      <span
        class="title-text"
        v-text="item.heading"
      />
      <span
        v-if="item.badgeContent"
        class="nav-item-badge"
        :class="item.badgeClass"
      >
        {{ item.badgeContent }}
      </span>
    </div>
  </li>
</template>

<style scoped lang="scss">
.title-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
</style>
