import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'translations',
    name: 'admin.translations.index',
    component: () => import('@/modules/translation/pages/admin/translation/Index.vue'),
    meta: {
      title: 'admin::sidebar.translations',
      icon: 'tabler-language',
      permission: 'admin.translations.index',
    },
  },
]

export default adminRoutes
