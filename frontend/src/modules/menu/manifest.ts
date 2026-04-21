import type { ModuleManifest } from '@/modules/core/contracts/ModuleManifest.ts'
import adminRoutes from '@/modules/menu/routes/admin.routes.ts'
import publicRoutes from '@/modules/menu/routes/public.routes.ts'
import { adminSidebar } from '@/modules/menu/sidebars/admin.sidebar.ts'

const manifest: ModuleManifest = {
  name: 'menu',
  routes: [
    {
      target: 'admin',
      routes: adminRoutes,
    },
    {
      target: 'public',
      routes: publicRoutes,
    },
  ],
  sidebars: [adminSidebar],
}

export default manifest
