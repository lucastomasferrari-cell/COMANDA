import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/giftcard/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/giftcard/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'giftcard',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
