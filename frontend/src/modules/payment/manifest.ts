import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/payment/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/payment/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'payment',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
