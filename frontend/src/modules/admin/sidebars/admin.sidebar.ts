import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.dashboard',
      label: 'admin::sidebar.dashboard',
      to: { name: 'admin.dashboard' },
      icon: 'tabler-layout-dashboard',
      sort: 1,
    },
  ],
}
