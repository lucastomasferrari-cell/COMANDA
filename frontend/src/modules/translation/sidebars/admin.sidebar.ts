import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.translations.index',
      label: 'admin::sidebar.translations',
      to: { name: 'admin.translations.index' },
      permission: 'admin.translations.index',
      parentKey: 'admin.localization',
      sort: 1,
    },
  ],
}
