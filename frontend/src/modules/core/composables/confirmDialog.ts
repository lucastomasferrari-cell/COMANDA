import { createApp, h } from 'vue'
import i18n from '@/app/plugins/i18n.ts'
import vuetify from '@/app/plugins/vuetify'
import ConfirmDialog from '@/modules/core/components/Dialogs/ConfirmDialog.vue'

export function useConfirmDialog (options?: {
  title?: string
  message?: string
  confirmButtonText?: string
  cancelButtonText?: string
  confirmColor?: string
}): Promise<boolean> {
  return new Promise(resolve => {
    const container = document.createElement('div')
    document.body.append(container)

    const app = createApp({
      render () {
        return h(ConfirmDialog, {
          'modelValue': true,
          ...options,
          'onUpdate:modelValue': (val: boolean) => {
            if (!val) {
              app.unmount()
              container.remove()
            }
          },
          'onClose': (confirmed: boolean) => {
            resolve(confirmed)
            app.unmount()
            container.remove()
          },
        })
      },
    })

    app.use(vuetify)
    app.use(i18n)
    app.mount(container)
  })
}
