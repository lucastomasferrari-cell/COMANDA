import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.pos',
      label: 'admin::sidebar.pos',
      icon: 'tabler-cash',
      permission: [
        'admin.pos_registers.index',
        'admin.pos_sessions.index',
        'admin.pos_cash_movements.index',
      ],
      sort: 3,
      children: [
        {
          key: 'admin.pos_registers.index',
          label: 'admin::sidebar.pos_registers',
          to: { name: 'admin.pos_registers.index' },
          permission: 'admin.pos_registers.index',
        },
        {
          key: 'admin.pos_sessions.index',
          label: 'admin::sidebar.pos_sessions',
          to: { name: 'admin.pos_sessions.index' },
          permission: 'admin.pos_sessions.index',
        },
        {
          key: 'admin.pos_cash_movements.index',
          label: 'admin::sidebar.pos_cash_movements',
          to: { name: 'admin.pos_cash_movements.index' },
          permission: 'admin.pos_cash_movements.index',
        },
      ],
    },
  ],
}
