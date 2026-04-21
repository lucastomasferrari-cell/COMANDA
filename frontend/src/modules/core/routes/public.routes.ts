import type { RouteRecordRaw } from 'vue-router'

const publicRoutes: RouteRecordRaw[] = [
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/modules/core/pages/errors/404.vue'),
    meta: {
      title: 'core::errors.404.title',
      target: 'public',
    },
  },
]

export default publicRoutes
