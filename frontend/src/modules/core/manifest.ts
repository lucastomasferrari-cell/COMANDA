import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import publicRoutes from '@/modules/core/routes/public.routes.ts'
import { adminSidebar } from '@/modules/core/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'core',
  sidebars: [adminSidebar],
  routes: [
    {
      target: 'public',
      routes: publicRoutes,
    },
  ],
}

export default manifest
