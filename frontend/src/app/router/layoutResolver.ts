import type { Component } from 'vue'
import type { TargetName } from '@/modules/core/contracts/Target'
import AdminLayout from '@/app/layouts/AdminLayout.vue'
import BlankLayout from '@/app/layouts/BlankLayout.vue'
import PosLayout from '@/app/layouts/PosLayout.vue'
import PublicLayout from '@/app/layouts/PublicLayout.vue'

export function resolveLayout (target?: TargetName): Component | void {
  if (target === 'admin') {
    return AdminLayout
  } else if (target === 'pos') {
    // Sprint 3.A.bis — layout dedicado para /admin/pos/* con header
    // compacto + main sin padding, sin el sidebar admin vertical.
    return PosLayout
  } else if (target === 'blank') {
    return BlankLayout
  }
  return PublicLayout
}
