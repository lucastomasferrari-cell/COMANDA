import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.settings.edit',
      label: 'admin::sidebar.settings',
      to: { name: 'admin.settings.edit' },
      icon: 'tabler-settings',
      permission: 'admin.settings.edit',
      sort: 105,
      parentKey: 'admin.heading.system',
    },
  ],
}
