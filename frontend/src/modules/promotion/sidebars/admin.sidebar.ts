import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.promotions',
      label: 'admin::sidebar.promotions',
      icon: 'tabler-target-arrow',
      permission: [
        'admin.discounts.index',
        'admin.vouchers.index',
      ],
      sort: 8,
      children: [
        {
          key: 'admin.discounts.index',
          label: 'admin::sidebar.discounts',
          to: { name: 'admin.discounts.index' },
          permission: 'admin.discounts.index',
        },
        {
          key: 'admin.vouchers.index',
          label: 'admin::sidebar.vouchers',
          to: { name: 'admin.vouchers.index' },
          permission: 'admin.vouchers.index',
        },
      ],
    },
  ],
}
