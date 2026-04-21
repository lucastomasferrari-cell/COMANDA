import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/tax/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/tax/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'tax',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
