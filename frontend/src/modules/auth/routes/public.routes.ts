import type { RouteRecordRaw } from 'vue-router'
import { guestMiddleware } from '@/modules/auth/middleware/guest.middleware.ts'

const publicRoutes: RouteRecordRaw[] = [
  {
    path: '/auth/login',
    name: 'auth.login',
    beforeEnter: guestMiddleware,
    component: () => import('@/modules/auth/pages/Login.vue'),
    meta: {
      target: 'blank',
      title: 'user::auth.login',
    },
  },
]

export default publicRoutes
