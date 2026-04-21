import type { RouteRecordRaw } from 'vue-router'
import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'
import type { TargetName } from '@/modules/core/contracts/Target.ts'
import { sidebarRegistry } from '@/modules/core/services/SidebarService.ts'
import { collectedModules } from '../../../app/bootstrap/collectModules.ts'

class ModuleService {
  private static instance: ModuleService
  private modules: Map<string, ModuleManifest> = new Map()

  private constructor () {
    collectedModules.forEach((module: ModuleManifest) => {
      this.modules.set(module.name, module)
      this.registerSidebars(module)
    })
  }

  static getInstance (): ModuleService {
    if (!ModuleService.instance) {
      ModuleService.instance = new ModuleService()
    }
    return ModuleService.instance
  }

  exists (name: string): boolean {
    return this.modules.has(name)
  }

  get (name: string): ModuleManifest | undefined {
    return this.modules.get(name)
  }

  all (): ModuleManifest[] {
    return Array.from(this.modules.values())
  }

  getRoutes = (): Record<TargetName, RouteRecordRaw[]> => {
    return this.all().reduce<Record<TargetName, RouteRecordRaw[]>>(
      (acc, module) => {
        if (module.routes) {
          for (const { target, routes } of module.routes) {
            acc[target] = acc[target]
              ? acc[target].concat(routes)
              : [...routes]
          }
        }
        return acc
      },
      { admin: [], public: [], blank: [] },
    )
  }

  private registerSidebars (module: ModuleManifest): void {
    module.sidebars?.forEach((sidebarList: SidebarList) => {
      sidebarRegistry.register(sidebarList.target, sidebarList.items)
    })
  }
}

export const moduleService = ModuleService.getInstance()
