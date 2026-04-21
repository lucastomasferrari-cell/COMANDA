import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/printer/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/printer/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'printer',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
