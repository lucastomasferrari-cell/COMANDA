import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.reports.index',
      label: 'admin::sidebar.reports',
      to: { name: 'admin.reports.index' },
      icon: 'tabler-chart-histogram',
      permission: 'admin.reports.index',
      sort: 103,
      parentKey: 'admin.heading.system',
    },
  ],
}
