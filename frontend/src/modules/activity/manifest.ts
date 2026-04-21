import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/activity/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/activity/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'activity',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
