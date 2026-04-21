import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: '/online-menu/:slug',
    name: 'public.online_menu.index',
    component: () => import('@/modules/menu/pages/public/onlineMenu/Index.vue'),
    meta: {
      target: 'public',
    },
  },
]

export default adminRoutes
