import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'media',
    name: 'admin.media',
    meta: {
      title: 'admin::sidebar.media',
      icon: 'tabler-photo-video',
    },
    children: [
      {
        path: '',
        name: 'admin.media.index',
        component: () => import('@/modules/media/pages/admin/media/Index.vue'),
        meta: {
          permission: 'admin.media.index',
        },
      },
    ],
  },
]

export default adminRoutes
