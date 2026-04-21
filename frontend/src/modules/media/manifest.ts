import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/media/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/media/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'media',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
