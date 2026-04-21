import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'

const modules = import.meta.glob<{
  default: ModuleManifest
}>('../../modules/**/manifest.ts', { eager: true })

export const collectedModules = Object.values(modules).map(m => m.default)
