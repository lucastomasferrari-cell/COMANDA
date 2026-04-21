import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/currency/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/currency/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'currency',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
