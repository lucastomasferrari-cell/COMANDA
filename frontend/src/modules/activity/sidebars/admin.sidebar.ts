import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.activities',
      label: 'admin::sidebar.activities',
      icon: 'tabler-history-toggle',
      permission: [
        'admin.activity_logs.index',
        'admin.authentication_logs.index',
      ],
      sort: 104,
      parentKey: 'admin.heading.system',
      children: [
        {
          key: 'admin.activity_logs.index',
          label: 'admin::sidebar.activity_logs',
          to: { name: 'admin.activity_logs.index' },
          permission: 'admin.activity_logs.index',
        },
        {
          key: 'admin.authentication_logs.index',
          label: 'admin::sidebar.authentication_logs',
          to: { name: 'admin.authentication_logs.index' },
          permission: 'admin.authentication_logs.index',
        },
      ],
    },
  ],
}
