import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/tool/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/tool/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'tool',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
