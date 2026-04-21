import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.heading.system',
      label: 'admin::sidebar.system',
      isHeading: true,
      permission: [],
      sort: 100,
    },
    {
      key: 'admin.localization',
      label: 'admin::sidebar.localization',
      icon: 'tabler-world',
      parentKey: 'admin.heading.system',
      permission: [],
      sort: 103,
      children: [],
    },
  ],

}
