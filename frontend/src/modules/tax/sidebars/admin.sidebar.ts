import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

// Preserved for rollback: consolidated in admin/sidebars/admin.sidebar.ts.
export const _legacyAdminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.taxes.index',
      label: 'admin::sidebar.taxes',
      to: { name: 'admin.taxes.index' },
      permission: 'admin.taxes.index',
      parentKey: 'admin.localization',
      sort: 3,
    },
  ],
}

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [],
}
