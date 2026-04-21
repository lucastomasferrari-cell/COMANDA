import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.sales',
      label: 'admin::sidebar.sales',
      icon: 'tabler-basket-dollar',
      permission: [
        'admin.orders.index',
        'admin.invoices.index',
        'admin.payments.index',
        'admin.reasons.index',
      ],
      sort: 2,
      children: [
        {
          key: 'admin.orders.index',
          label: 'admin::sidebar.orders',
          to: { name: 'admin.orders.index' },
          permission: 'admin.orders.index',
        },
        {
          key: 'admin.invoices.index',
          label: 'admin::sidebar.invoices',
          to: { name: 'admin.invoices.index' },
          permission: 'admin.invoices.index',
        },
        {
          key: 'admin.payments.index',
          label: 'admin::sidebar.payments',
          to: { name: 'admin.payments.index' },
          permission: 'admin.payments.index',
        },
        {
          key: 'admin.reasons.index',
          label: 'admin::sidebar.reasons',
          to: { name: 'admin.reasons.index' },
          permission: 'admin.reasons.index',
        },
      ],
    },
  ],
}
