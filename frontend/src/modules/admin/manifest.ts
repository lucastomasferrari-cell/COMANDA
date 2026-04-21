import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/admin/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/admin/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'admin',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
