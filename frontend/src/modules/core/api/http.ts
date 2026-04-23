import type { RouteLocationRaw } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '@/modules/auth/stores/authStore.ts'
import { useAppStore } from '@/modules/core/stores/appStore.ts'

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
})

http.interceptors.request.use(config => {
  const auth = useAuthStore()
  const app = useAppStore()

  if (!config.headers.Authorization && auth.getToken) {
    config.headers.Authorization = `Bearer ${auth.getToken}`
  }

  config.headers['X-Comanda-Locale'] = app.appCurrentLocale || 'en'
  return config
})

http.interceptors.response.use(
  response => response,
  async error => {
    // Anti-fraude: 403 con shape { code: 'manager_approval_required' }
    // o header X-AntiFraud-Action → abrimos el PIN dialog, metemos el
    // token en el body y reintentamos la misma request una sola vez.
    const isAntifraudApproval =
      error.response?.status === 403
      && (
        error.response.data?.code === 'manager_approval_required'
        || error.response.headers?.['x-antifraud-action'] === 'manager_approval_required'
      )
      && !(error.config as any)?.__antifraudRetried

    if (isAntifraudApproval) {
      const { useManagerApprovalStore } = await import('@/modules/auth/stores/managerApprovalStore.ts')
      const store = useManagerApprovalStore()

      const token = await store.requestApproval({
        actionContext: error.response.data?.context ?? 'anti_fraud',
        subtitle: error.response.data?.message,
      })

      if (!token) {
        throw error // user canceló
      }

      const retryConfig: any = { ...error.config, __antifraudRetried: true }
      let body: any = retryConfig.data
      if (typeof body === 'string') {
        try { body = JSON.parse(body) } catch { body = {} }
      }
      body = { ...(body || {}), manager_approval_token: token }
      retryConfig.data = body
      return http.request(retryConfig)
    }

    if (error.response?.status === 401 && (!error?.config?.url?.includes('/auth/login'))) {
      if (error?.config?.url?.includes('/auth/check')) {
        throw error
      }

      const auth = useAuthStore()
      await auth.logout()
      if (auth.isAuthenticated) {
        await useRouter().push({ name: 'admin.dashboard' } as unknown as RouteLocationRaw)
      } else {
        await useRouter().push({ name: 'auth.login' } as unknown as RouteLocationRaw)
      }
    } else {
      throw error
    }
  },
)
