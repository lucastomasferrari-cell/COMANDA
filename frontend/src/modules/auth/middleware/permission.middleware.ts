import type {
  NavigationGuardNext,
  RouteLocationNormalized,
  RouteLocationNormalizedLoaded,
  RouteLocationRaw,
} from 'vue-router'
import { useAuth } from '@/modules/auth/composables/auth.ts'

export function permissionMiddleware (to: RouteLocationNormalized, from: RouteLocationNormalizedLoaded, next: NavigationGuardNext) {
  const auth = useAuth()

  if ((typeof to.meta.permission == 'string' || Array.isArray(to.meta.permission))
    && !auth.hasPermission(to.meta?.permission)) {
    next({ name: 'admin.dashboard' } as unknown as RouteLocationRaw)
  } else {
    next()
  }
}
