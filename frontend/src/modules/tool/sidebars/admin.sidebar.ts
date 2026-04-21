import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.tools',
      label: 'admin::sidebar.tools',
      icon: 'tabler-tools',
      parentKey: 'admin.heading.system',
      permission: [],
      sort: 102,
      children: [
        {
          key: 'admin.database_tools.index',
          label: 'admin::sidebar.database',
          to: { name: 'admin.tools.database' },
          icon: 'tabler-database',
          permission: 'admin.database_tools.index',
          sort: 1,
        },
      ],
    },
  ],
}
