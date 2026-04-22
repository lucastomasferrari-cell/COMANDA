import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

// Preserved for rollback: consolidated in admin/sidebars/admin.sidebar.ts.
export const _legacyAdminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.media.index',
      label: 'admin::sidebar.media',
      to: { name: 'admin.media.index' },
      icon: 'tabler-photo-video',
      permission: 'admin.media.index',
      sort: 10,
    },
  ],
}

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [],
}
