import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/loyalty/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/loyalty/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'loyalty',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
