import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'activity-logs',
    name: 'admin.activity_logs',
    meta: {
      title: 'admin::sidebar.activity_logs',
      icon: 'tabler-history-toggle',
    },
    children: [
      {
        path: '',
        name: 'admin.activity_logs.index',
        component: () => import('@/modules/activity/pages/admin/activityLog/Index.vue'),
        meta: {
          permission: 'admin.activity_logs.index',
        },
      },
      {
        path: ':id/show',
        name: 'admin.activity_logs.show',
        component: () => import('@/modules/activity/pages/admin/activityLog/Show.vue'),
        meta: {
          title: 'admin::resource.show',
          transParam: { resource: 'activitylog::activity_logs.activity_log' },
          permission: 'admin.activity_logs.show',
        },
      },
    ],
  },
  {
    path: 'authentication-logs',
    name: 'admin.authentication_logs.index',
        component: () => import('@/modules/activity/pages/admin/authenticationLog/Index.vue'),
    meta: {
      title: 'admin::sidebar.authentication_logs',
      permission: 'admin.authentication_logs.index',
      icon: 'tabler-clock-shield',
    },
  },
]

export default adminRoutes
