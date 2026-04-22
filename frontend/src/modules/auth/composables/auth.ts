import { type RouteLocationRaw, useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import * as auth from '@/modules/auth/api/auth.api.ts'
import { useAuthStore } from '@/modules/auth/stores/authStore.ts'

export function useAuth () {
  const authStore = useAuthStore()
  const router = useRouter()
  const route = useRoute()

  const login = async (identifier: string, password: string) => {
    const response = await auth.login(identifier, password)
    authStore.setAuth(response.data.body)
    let name = 'admin.dashboard'
    let params = {}
    if ((authStore.hasRole('cashier') || authStore.hasRole('waiter')) && authStore.can('admin.pos.index')) {
      name = 'admin.pos.index'
      params = { cartId: crypto.randomUUID() }
    } else if (authStore.hasRole('kitchen') && authStore.can('admin.pos.kitchen_viewer')) {
      name = 'admin.pos.kitchen_viewer'
    }
    await router.push({ name, params } as unknown as RouteLocationRaw)
  }

  const logout = async () => {
    try {
      useToast().success((await auth.logout()).data.message)
    } catch {
      /* empty */
    }

    await authStore.logout()
    // 9.1: al logout siempre a /login, aunque queden otras cuentas
    // autenticadas (multi-account). Simplifica el UX — el multi-account
    // switcher sigue funcionando desde la pantalla de login si hace falta.
    await router.push({ name: 'auth.login' } as unknown as RouteLocationRaw)
  }

  const logoutAll = async () => {
    const tokens = [...new Set(authStore.getAccounts.map(account => account.token).filter(Boolean))]
    await Promise.allSettled(tokens.map(token => auth.logoutByToken(token)))
    await authStore.logoutAll()
    await router.push({ name: 'auth.login' } as unknown as RouteLocationRaw)
  }

  const switchAccount = async (accountId: number) => {
    const switched = authStore.switchAccount(accountId)
    if (!switched) {
      return
    }

    try {
      await auth.check()
      if (route.name !== 'admin.dashboard') {
        await router.push({ name: 'admin.dashboard' } as unknown as RouteLocationRaw)
      }
    } catch {
      await authStore.logout()
      if (!authStore.isAuthenticated) {
        await router.push({ name: 'auth.login' } as unknown as RouteLocationRaw)
      }
    }
  }

  const goToAddAccount = async () => {
    await router.push({
      name: 'auth.login',
      query: { add_account: '1' },
    } as unknown as RouteLocationRaw)
  }

  const check = async () => {
    try {
      await auth.check()
      return true
    } catch {
      return false
    }
  }

  return {
    store: authStore,
    user: authStore.getUser,
    login,
    logout,
    logoutAll,
    switchAccount,
    goToAddAccount,
    check,
    can: authStore.can,
    canAny: authStore.canAny,
    hasPermission: authStore.hasPermission,
    hasRole: authStore.hasRole,
    hasAnyRoles: authStore.hasAnyRoles,
  }
}
