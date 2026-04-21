import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.menus',
      label: 'admin::sidebar.menus',
      icon: 'tabler-list-details',
      permission: [
        'admin.menus.index',
        'admin.online_menus.index',
        'admin.categories.index',
        'admin.products.index',
        'admin.options.index',
      ],
      sort: 4,
      children: [
        {
          key: 'admin.menus.index',
          label: 'admin::sidebar.all_menus',
          to: { name: 'admin.menus.index' },
          permission: 'admin.menus.index',
        },
        {
          key: 'admin.online_menus.index',
          label: 'admin::sidebar.online_menus',
          to: { name: 'admin.online_menus.index' },
          permission: 'admin.online_menus.index',
        },
        {
          key: 'admin.categories.index',
          label: 'admin::sidebar.categories',
          to: { name: 'admin.categories.index' },
          permission: 'admin.categories.index',
        },
        {
          key: 'admin.products.index',
          label: 'admin::sidebar.products',
          to: { name: 'admin.products.index' },
          permission: 'admin.products.index',
        },
        {
          key: 'admin.options.index',
          label: 'admin::sidebar.options',
          to: { name: 'admin.options.index' },
          permission: 'admin.options.index',
        },
      ],
    },
  ],
}
