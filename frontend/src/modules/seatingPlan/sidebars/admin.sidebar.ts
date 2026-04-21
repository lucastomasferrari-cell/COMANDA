import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.seating_plan',
      label: 'admin::sidebar.seating_plan',
      icon: 'tabler-building-skyscraper',
      permission: [
        'admin.tables.index',
        'admin.table_merges.index',
        'admin.zones.index',
        'admin.floors.index',
      ],
      sort: 6,
      children: [
        {
          key: 'admin.tables.index',
          label: 'admin::sidebar.tables',
          to: { name: 'admin.tables.index' },
          permission: 'admin.tables.index',
        },
        {
          key: 'admin.table_merges.index',
          label: 'admin::sidebar.table_merges',
          to: { name: 'admin.table_merges.index' },
          permission: 'admin.table_merges.index',
        },
        {
          key: 'admin.zones.index',
          label: 'admin::sidebar.zones',
          to: { name: 'admin.zones.index' },
          permission: 'admin.zones.index',
        },
        {
          key: 'admin.floors.index',
          label: 'admin::sidebar.floors',
          to: { name: 'admin.floors.index' },
          permission: 'admin.floors.index',
        },
      ],
    },
  ],
}
