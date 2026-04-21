import type { RouteRecordRaw } from 'vue-router'
import { reportAccessMiddleware } from '@/modules/report/middleware/reportAccess.ts'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'reports',
    name: 'admin.reports',
    meta: {
      title: 'admin::sidebar.reports',
      icon: 'tabler-chart-histogram',
    },
    children: [
      {
        path: '',
        name: 'admin.reports.index',
        component: () => import('@/modules/report/pages/admin/report/Index.vue'),
        meta: {
          permission: 'admin.reports.index',
        },
      },
      {
        path: ':key',
        name: 'admin.reports.show',
        beforeEnter: reportAccessMiddleware,
        component: () => import('@/modules/report/pages/admin/report/Show.vue'),
        meta: {
          permission: 'admin.reports.index',
        },
      },
    ],
  },
]

export default adminRoutes
