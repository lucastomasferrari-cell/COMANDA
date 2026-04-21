import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/branch/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/branch/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'branch',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
