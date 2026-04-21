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

  config.headers['X-Forkiva-Locale'] = app.appCurrentLocale || 'en'
  return config
})

http.interceptors.response.use(
  response => response,
  async error => {
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
