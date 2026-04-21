import type { App } from 'vue'
import type { Router } from 'vue-router'

import axios from 'axios'
import { setupBootData } from '@/app/bootstrap/setupBootData'
import { setupI18n, setupPwa, setupToast, setupVuetify } from '@/app/plugins'
import { check } from '@/modules/auth/api/auth.api.ts'
import { useAuthStore } from '@/modules/auth/stores/authStore.ts'
import EnglishNumbersWithDecimal from '@/modules/core/directives/english-numbers-with-decimal.ts'
import EnglishNumbers from '@/modules/core/directives/english-numbers.ts'
import { setupStore } from '@/modules/core/stores'
import './collectModules'

interface InitAppOptions {
  app: App
  router: Router
}

async function setupAuthSession (): Promise<void> {
  const authStore = useAuthStore()
  await authStore.restoreAuth()

  while (authStore.isAuthenticated) {
    try {
      const response = await check()
      authStore.setUser(response?.data?.body?.user)
      break
    } catch (error) {
      if ((axios.isAxiosError(error) && error.response?.status === 401) || !authStore.getUser) {
        await authStore.logout()
        continue
      }

      break
    }
  }
}

export async function initApp (_: InitAppOptions): Promise<void> {
  setupStore(_.app)

  _.app.directive('integer-en', EnglishNumbers)
  _.app.directive('decimal-en', EnglishNumbersWithDecimal)

  await setupBootData()

  setupI18n(_.app)
  setupVuetify(_.app)
  setupToast(_.app)
  setupPwa()

  await setupAuthSession()
}
