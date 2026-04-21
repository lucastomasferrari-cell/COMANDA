import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/user/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/user/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'user',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
