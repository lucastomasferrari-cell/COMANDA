import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
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
