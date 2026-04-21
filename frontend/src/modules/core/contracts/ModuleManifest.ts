import type { RouteRecordRaw } from 'vue-router'
import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'
import type { TargetName } from '@/modules/core/contracts/Target.ts'

export interface ModuleManifestRoutes {
  target: TargetName
  routes: RouteRecordRaw[]
}

export interface ModuleManifest {
  name: string
  version?: string
  routes?: ModuleManifestRoutes[]
  sidebars?: SidebarList[]
}
