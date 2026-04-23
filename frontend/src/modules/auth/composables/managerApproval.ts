import { ref } from 'vue'

/**
 * Coordina el ManagerPinDialog como un flow promise-based:
 *
 *   const { requestApproval, dialogProps, handlers } = useManagerApproval()
 *   const token = await requestApproval({ actionContext: 'void_item_after_fire' })
 *   if (token) { ... envía con X-Manager-Approval: token ... }
 *
 * Una sola instancia de dialog por viewer; el caller monta
 * ManagerPinDialog con `v-bind="dialogProps" v-on="handlers"`.
 */
export function useManagerApproval () {
  const open = ref(false)
  const subtitle = ref<string | undefined>(undefined)
  const actionContext = ref<string | undefined>(undefined)
  let resolver: ((token: string | null) => void) | null = null

  function requestApproval (opts: { actionContext?: string, subtitle?: string } = {}): Promise<string | null> {
    actionContext.value = opts.actionContext
    subtitle.value = opts.subtitle
    open.value = true
    return new Promise(resolve => {
      resolver = resolve
    })
  }

  function onApproved (token: string) {
    open.value = false
    resolver?.(token)
    resolver = null
  }

  function onCancelled () {
    open.value = false
    resolver?.(null)
    resolver = null
  }

  function onUpdateModel (val: boolean) {
    open.value = val
    if (!val && resolver) {
      resolver(null)
      resolver = null
    }
  }

  return {
    dialogProps: {
      modelValue: open,
      actionContext,
      subtitle,
    },
    handlers: {
      approved: onApproved,
      cancelled: onCancelled,
      'update:modelValue': onUpdateModel,
    },
    requestApproval,
  }
}
