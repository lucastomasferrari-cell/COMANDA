import { defineStore } from 'pinia'
import { ref } from 'vue'

/**
 * Singleton state del Manager PIN dialog. El ManagerPinDialog vive
 * una sola vez mounted en el layout, y cualquier caller (incluido
 * el axios interceptor) puede pedirle token a través de este store.
 *
 * Uso desde caller:
 *   const store = useManagerApprovalStore()
 *   const token = await store.requestApproval('void_item_after_fire')
 *   if (!token) throw new Error('cancelled')
 *   // usar token en body del retry
 *
 * El dialog observa store.open y al resolver llama store.resolve(token|null).
 */
export const useManagerApprovalStore = defineStore('managerApproval', () => {
  const open = ref(false)
  const actionContext = ref<string | null>(null)
  const subtitle = ref<string | null>(null)
  let resolver: ((token: string | null) => void) | null = null

  function requestApproval (opts: { actionContext?: string, subtitle?: string } = {}): Promise<string | null> {
    // Si ya hay un pedido en curso, encadenamos el nuevo detrás
    if (resolver) {
      resolver(null) // cancelamos el previo
    }
    actionContext.value = opts.actionContext ?? null
    subtitle.value = opts.subtitle ?? null
    open.value = true
    return new Promise(resolve => {
      resolver = resolve
    })
  }

  function resolveWith (token: string | null) {
    open.value = false
    const fn = resolver
    resolver = null
    fn?.(token)
  }

  function cancel () { resolveWith(null) }
  function approve (token: string) { resolveWith(token) }

  return {
    open,
    actionContext,
    subtitle,
    requestApproval,
    approve,
    cancel,
  }
})
