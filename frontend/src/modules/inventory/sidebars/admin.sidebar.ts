import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.inventory',
      label: 'admin::sidebar.inventory',
      icon: 'ic-outline-inventory-2',
      permission: [
        'admin.units.index',
        'admin.suppliers.index',
        'admin.ingredients.index',
        'admin.stock_movements.index',
        'admin.purchases.index',
        'admin.analytics.index',
      ],
      sort: 7,
      children: [
        {
          key: 'admin.inventories.analytics',
          label: 'admin::sidebar.analytics',
          to: { name: 'admin.inventories.analytics' },
          permission: 'admin.inventories.analytics',
        },
        {
          key: 'admin.units.index',
          label: 'admin::sidebar.units',
          to: { name: 'admin.units.index' },
          permission: 'admin.units.index',
        },
        {
          key: 'admin.suppliers.index',
          label: 'admin::sidebar.suppliers',
          to: { name: 'admin.suppliers.index' },
          permission: 'admin.suppliers.index',
        },
        {
          key: 'admin.ingredients.index',
          label: 'admin::sidebar.ingredients',
          to: { name: 'admin.ingredients.index' },
          permission: 'admin.ingredients.index',
        },
        {
          key: 'admin.stock_movements.index',
          label: 'admin::sidebar.stock_movements',
          to: { name: 'admin.stock_movements.index' },
          permission: 'admin.stock_movements.index',
        },
        {
          key: 'admin.purchases.index',
          label: 'admin::sidebar.purchases',
          to: { name: 'admin.purchases.index' },
          permission: 'admin.purchases.index',
        },
      ],
    },
  ],
}
