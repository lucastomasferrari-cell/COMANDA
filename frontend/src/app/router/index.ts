import type { RouteRecordRaw } from 'vue-router'
import { authMiddleware } from '@/modules/auth/middleware/auth.middleware.ts'
import { permissionMiddleware } from '@/modules/auth/middleware/permission.middleware.ts'
import { moduleService } from '@/modules/core/services/ModuleService.ts'

const moduleRoutes = moduleService.getRoutes()

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/admin',
  },
  {
    path: `/admin`,
    beforeEnter: [authMiddleware, permissionMiddleware],
    meta: {
      target: 'admin',
    },
    children: moduleRoutes['admin'],
  },
  ...(moduleRoutes['public'] ?? []),
]

export default routes
