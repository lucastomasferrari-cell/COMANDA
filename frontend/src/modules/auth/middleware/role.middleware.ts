import type {
  NavigationGuardNext,
  RouteLocationNormalized,
  RouteLocationNormalizedLoaded,
  RouteLocationRaw,
} from 'vue-router'
import { useAuth } from '@/modules/auth/composables/auth.ts'

export function roleMiddleware (to: RouteLocationNormalized, from: RouteLocationNormalizedLoaded, next: NavigationGuardNext) {
  const auth = useAuth()

  if ((typeof to.meta.role == 'string' || Array.isArray(to.meta.role)) && !auth.hasRole(to.meta.role)) {
    next({ name: 'admin.dashboard' } as unknown as RouteLocationRaw)
  } else {
    next()
  }
}
