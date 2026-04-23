import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

// Payment methods viven como tab del hub Cobros definido en
// admin/sidebars/admin.sidebar.ts. Este archivo queda vacío (estructura
// del vendor) — los sidebar items consolidados viven en el manifest
// del módulo admin.
export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [],
}
