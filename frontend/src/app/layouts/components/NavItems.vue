<script lang="ts" setup>
import {computed} from 'vue'
import {useI18n} from 'vue-i18n'

import {useAuth} from '@/modules/auth/composables/auth'
import VerticalNavLink from '@/app/layouts/components/VerticalNavLink.vue'
import VerticalNavGroup from '@/app/layouts/components/VerticalNavGroup.vue'
import VerticalNavSectionTitle from '@/app/layouts/components/VerticalNavSectionTitle.vue'

import type {TargetName} from '@/modules/core/contracts/Target'
import {sidebarRegistry} from '@/modules/core/services/SidebarService.ts'
import type {SidebarItem} from "@/modules/core/contracts/SidebarItem.ts";


const props = defineProps<{
  target: TargetName
}>()

const {t} = useI18n()
const {hasPermission} = useAuth()


const isAllowed = (permission?: string[] | string): boolean => !permission || hasPermission(permission)

const mapLinkItem = (item: SidebarItem) => ({
  title: t(item.label),
  to: item.to,
  icon: item.icon || undefined,
  href: item.href,
  target: item.target,
  disable: item.disable,
  badgeContent: item.badgeContent,
  badgeClass: item.badgeClass,
})

const sidebarItems = computed(() =>
  sidebarRegistry
    .build(props.target)
    .filter(item => isAllowed(item.permission))
)
</script>

<template>
  <template v-for="item in sidebarItems" :key="item.key">
    <VerticalNavSectionTitle
      v-if="item.isHeading"
      :item="{ heading: t(item.label), badgeContent: item.badgeContent, badgeClass: item.badgeClass }"
    />

    <VerticalNavLink
      v-else-if="!item.children || item.children.length === 0"
      :item="mapLinkItem(item)"
    />

    <VerticalNavGroup
      v-else
      :item="{ title: t(item.label), icon: item.icon }"
    >
      <template v-for="subItem in item.children" :key="subItem.key">
        <VerticalNavLink
          v-if="isAllowed(subItem.permission)"
          :item="mapLinkItem(subItem)"
        />
      </template>
    </VerticalNavGroup>

  </template>
</template>
