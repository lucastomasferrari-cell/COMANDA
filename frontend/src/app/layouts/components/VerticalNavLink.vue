<script lang="ts" setup>

  import type { NavLink } from '@/modules/core/contracts/Theme.ts'

  const props = defineProps<{ item: NavLink }>()
  const route = useRoute()

  const normalizeName = (name?: string | symbol | null) => {
    if (typeof name === 'string') {
      return name.split('.').filter(Boolean).slice(0, 2).join('.')
    }
    if (typeof name === 'symbol') {
      return (name.description || '').split('.').filter(Boolean).slice(0, 2).join('.')
    }
    return ''
  }

  const isActive = computed(() => {
    return normalizeName(route.name) === normalizeName(props.item.to?.name ?? null)
  })

</script>

<template>
  <li
    class="nav-link"
    :class="{ disabled: item.disable }"
  >
    <Component
      :is="item.to ? 'RouterLink' : 'a'"
      :class="{'router-main-link-active':isActive}"
      :href="item.href"
      :target="item.target"
      :to="item.to"
    >
      <VIcon
        class="nav-item-icon"
        :icon="item.icon || 'tabler-circle-filled'"
      />
      <!-- 👉 Title -->
      <span class="nav-item-title">
        {{ item.title }}
      </span>
      <span
        class="nav-item-badge"
        :class="item.badgeClass"
      >
        {{ item.badgeContent }}
      </span>
    </Component>
  </li>
</template>

<style lang="scss">
.layout-vertical-nav {
  .nav-link a {
    display: flex;
    align-items: center;
    cursor: pointer;
  }
}
</style>
