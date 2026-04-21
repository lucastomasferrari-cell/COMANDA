import type { App } from 'vue'
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

export function setupToast (app: App<Element>) {
  app.use(Toast)
}
