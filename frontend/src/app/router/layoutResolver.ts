import type { Component } from 'vue'
import type { TargetName } from '@/modules/core/contracts/Target'
import AdminLayout from '@/app/layouts/AdminLayout.vue'
import BlankLayout from '@/app/layouts/BlankLayout.vue'
import PublicLayout from '@/app/layouts/PublicLayout.vue'

export function resolveLayout (target?: TargetName): Component | void {
  if (target === 'admin') {
    return AdminLayout
  } else if (target === 'blank') {
    return BlankLayout
  }
  return PublicLayout
}
