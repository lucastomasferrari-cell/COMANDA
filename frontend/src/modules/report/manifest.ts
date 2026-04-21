import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/report/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/report/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'report',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
