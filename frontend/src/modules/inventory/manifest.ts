import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/inventory/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/inventory/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'inventory',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
