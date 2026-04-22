import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

// Preserved for rollback: consolidated in admin/sidebars/admin.sidebar.ts.
export const _legacyAdminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.currency_rates.index',
      label: 'admin::sidebar.currency_rates',
      to: { name: 'admin.currency_rates.index' },
      permission: 'admin.currency_rates.index',
      parentKey: 'admin.localization',
      sort: 2,
    },
  ],
}

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [],
}
