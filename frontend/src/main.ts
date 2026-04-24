import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { initApp } from './app/bootstrap/initApp'

import routes from './app/router'
import Comanda from './Comanda.vue'
import '@/assets/css.ts'

const app = createApp(Comanda)

// Sprint 3.A.bis post-validación 8 — instrumentación temporal para
// identificar el componente que dispara __vnode crashes. Los stack
// traces de Vue solo mostraban internals (runtime-core.esm-bundler.js),
// sin apuntar a ningún .vue. El errorHandler nos da el tipo del
// componente que estaba patcheando cuando falló.
//
// Remover este handler después de validar en browser + aplicar los
// fixes puntuales a los componentes que aparezcan en el log.
app.config.errorHandler = (err: unknown, instance, info) => {
  const message = (err as Error)?.message ?? String(err)
  const stack = (err as Error)?.stack
  const type = (instance?.$ as any)?.type ?? (instance as any)?.type ?? null
  const componentName = type?.__name ?? type?.name ?? 'anonymous'
  const componentFile = type?.__file ?? 'unknown'

  if (message?.includes('__vnode') || message?.includes('Cannot set properties of null')) {
    // eslint-disable-next-line no-console
    console.error('[__vnode crash]', {
      component: componentName,
      file: componentFile,
      info,
      message,
      stack,
    })
    return
  }

  // eslint-disable-next-line no-console
  console.error('[vue error]', {
    component: componentName,
    file: componentFile,
    info,
    message,
  }, err)
}

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
