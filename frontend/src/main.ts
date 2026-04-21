import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { initApp } from './app/bootstrap/initApp'

import routes from './app/router'
import Forkiva from './Forkiva.vue'
import '@/assets/css.ts'

const app = createApp(Forkiva)

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.onError((err, to) => {
  if (err?.message?.includes?.('Failed to fetch dynamically imported module')) {
    if (localStorage.getItem('vuetify:dynamic-reload')) {} else {
      localStorage.setItem('vuetify:dynamic-reload', 'true')
      location.assign(to.fullPath)
    }
  }
})

router.isReady().then(() => {
  localStorage.removeItem('vuetify:dynamic-reload')
})

await initApp({ app, router })

app.use(router)
app.mount('#app')
