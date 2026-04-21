import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/sale/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/sale/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'sale',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
