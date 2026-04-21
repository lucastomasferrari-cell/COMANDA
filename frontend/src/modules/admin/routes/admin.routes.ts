import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: '',
    name: 'admin.dashboard',
    component: () => import('@/modules/admin/pages/admin/dashboard/Index.vue'),
    meta: {
      title: 'admin::sidebar.dashboard',
      pageHeader: false,
    },
  },
]

export default adminRoutes
