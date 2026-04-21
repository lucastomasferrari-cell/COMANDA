import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'tools/database',
    name: 'admin.tools.database',
    component: () => import('@/modules/tool/pages/admin/database/Index.vue'),
    meta: {
      title: 'tool::database.database',
      icon: 'tabler-database',
      permission: 'admin.database_tools.index',
    },
  },
]

export default adminRoutes
