import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/promotion/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/promotion/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'promotion',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
