import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.branches.index',
      label: 'admin::sidebar.branches',
      to: { name: 'admin.branches.index' },
      icon: 'tabler-git-branch',
      permission: 'admin.branches.index',
      sort: 5,
    },
  ],
}
