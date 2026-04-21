<script lang="ts" setup>
  import type { NavGroup } from '@/modules/core/contracts/Theme.ts'

  defineProps<{ item: Omit<NavGroup, 'children'> }>()
  const isOpen = ref(false)
  const groupRef = ref<HTMLElement | null>(null)
  onMounted(() => {
    if (groupRef.value) {
      const links = [
        ...groupRef.value.querySelectorAll('.router-link-exact-active'),
        ...groupRef.value.querySelectorAll('.router-main-link-active'),
      ]
      isOpen.value = links.length > 0
    }
  })
</script>

<template>
  <li
    ref="groupRef"
    class="nav-group"
    :class="isOpen && 'open'"
  >
    <div
      class="nav-group-label"
      @click="isOpen = !isOpen"
    >
      <VIcon
        class="nav-item-icon"
      >
        {{ item?.icon || 'tabler-circle-filled' }}
      </VIcon>
      <span class="nav-item-title">{{ item.title }}</span>
      <span
        class="nav-item-badge"
        :class="item.badgeClass"
      >
        {{ item.badgeContent }}
      </span>
      <VIcon
        class="nav-group-arrow"
        icon="tabler-chevron-right"
      />
    </div>
    <div class="nav-group-children-wrapper">
      <ul class="nav-group-children">
        <slot />
      </ul>
    </div>
  </li>
</template>

<style lang="scss">
.layout-vertical-nav {
  .nav-group {
    &-label {
      display: flex;
      align-items: center;
      cursor: pointer;
    }

    .nav-group-children-wrapper {
      display: grid;
      grid-template-rows: 0fr;
      transition: grid-template-rows 0.3s ease-in-out;

      .nav-group-children {
        overflow: hidden;
      }
    }

    &.open {
      .nav-group-children-wrapper {
        grid-template-rows: 1fr;
      }
    }
  }
}
</style>
