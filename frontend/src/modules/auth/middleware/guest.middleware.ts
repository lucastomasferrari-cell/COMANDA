import type {
  NavigationGuardNext,
  RouteLocationNormalized,
  RouteLocationNormalizedLoaded,
  RouteLocationRaw,
} from 'vue-router'
import { useAuth } from '@/modules/auth/composables/auth.ts'

export function guestMiddleware (to: RouteLocationNormalized, from: RouteLocationNormalizedLoaded, next: NavigationGuardNext) {
  const auth = useAuth()
  const isAddAccount = to.query?.add_account === '1'

  return (async () => {
    if (!auth.store.getToken) {
      await auth.store.restoreAuth()
    }

    if (auth.store.isAuthenticated && !isAddAccount) {
      next({ name: 'admin.dashboard' } as unknown as RouteLocationRaw)
    } else {
      next()
    }
  })()
}
