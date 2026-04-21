import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/pos/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/pos/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'pos',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
