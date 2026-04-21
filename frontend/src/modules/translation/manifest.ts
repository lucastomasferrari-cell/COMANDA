import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/translation/routes/admin.routes.ts'
import { adminSidebar } from '@/modules/translation/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'translation',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
  ],
  sidebars: [adminSidebar],

}

export default manifest
