import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.loyalty',
      label: 'admin::sidebar.loyalty',
      icon: 'tabler-gift',
      permission: [
        'admin.loyalty_programs.index',
        'admin.loyalty_tiers.index',
        'admin.loyalty_customers.index',
        'admin.loyalty_transactions.index',
        'admin.loyalty_rewards.index',
        'admin.loyalty_promotions.index',
        'admin.loyalty_gifts.index',
      ],
      sort: 9,
      children: [
        {
          key: 'admin.loyalty_programs.index',
          label: 'admin::sidebar.loyalty_programs',
          to: { name: 'admin.loyalty_programs.index' },
          permission: 'admin.loyalty_programs.index',
        },
        {
          key: 'admin.loyalty_tiers.index',
          label: 'admin::sidebar.loyalty_tiers',
          to: { name: 'admin.loyalty_tiers.index' },
          permission: 'admin.loyalty_tiers.index',
        },
        {
          key: 'admin.loyalty_rewards.index',
          label: 'admin::sidebar.loyalty_rewards',
          to: { name: 'admin.loyalty_rewards.index' },
          permission: 'admin.loyalty_rewards.index',
        },
        {
          key: 'admin.loyalty_promotions.index',
          label: 'admin::sidebar.loyalty_promotions',
          to: { name: 'admin.loyalty_promotions.index' },
          permission: 'admin.loyalty_promotions.index',
        },
        {
          key: 'admin.loyalty_customers.index',
          label: 'admin::sidebar.loyalty_customers',
          to: { name: 'admin.loyalty_customers.index' },
          permission: 'admin.loyalty_customers.index',
        },
        {
          key: 'admin.loyalty_gifts.index',
          label: 'admin::sidebar.loyalty_gifts',
          to: { name: 'admin.loyalty_gifts.index' },
          permission: 'admin.loyalty_gifts.index',
        },
        {
          key: 'admin.loyalty_transactions.index',
          label: 'admin::sidebar.loyalty_transactions',
          to: { name: 'admin.loyalty_transactions.index' },
          permission: 'admin.loyalty_transactions.index',
        },
      ],
    },
  ],
}
