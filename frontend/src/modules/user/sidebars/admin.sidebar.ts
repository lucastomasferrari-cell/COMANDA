import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.users_and_roles_management',
      label: 'admin::sidebar.users',
      icon: 'tabler-users-group',
      permission: [
        'admin.users.index',
        'admin.customers.index',
        'admin.roles.index',
      ],
      sort: 101,
      parentKey: 'admin.heading.system',
      children: [
        {
          key: 'admin.users.index',
          label: 'admin::sidebar.users',
          to: { name: 'admin.users.index' },
          permission: 'admin.users.index',
        },
        {
          key: 'admin.customers.index',
          label: 'admin::sidebar.customers',
          to: { name: 'admin.customers.index' },
          permission: 'admin.customers.index',
        },
        {
          key: 'admin.roles.index',
          label: 'admin::sidebar.roles',
          to: { name: 'admin.roles.index' },
          permission: 'admin.roles.index',
        },
      ],
    },
  ],
}
