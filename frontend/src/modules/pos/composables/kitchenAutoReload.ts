import { type ComputedRef, onMounted, onUnmounted, ref, watch } from 'vue'

export interface KitchenAutoReloadSettings {
  enabled: boolean
  mode: 'smart_polling'
  interval: number
  pause_on_idle: boolean
  idle_timeout: number
}

export interface KitchenFetchResponse {
  last_updated_at: string | null
}

interface UseKitchenAutoReloadOptions {
  settings: ComputedRef<KitchenAutoReloadSettings | null>
  fetchData: () => Promise<KitchenFetchResponse>
}

export function useKitchenAutoReload ({ settings, fetchData }: UseKitchenAutoReloadOptions) {
  const timer = ref<number | null>(null)
  const idleTimer = ref<number | null>(null)
  const lastHash = ref<string | null>(null)
  const isPaused = ref<boolean>(false)

  const stop = (): void => {
    if (timer.value !== null) {
      window.clearInterval(timer.value)
      timer.value = null
    }
  }

  const start = (): void => {
    const config = settings.value

    if (!config || !config.enabled || config.mode !== 'smart_polling') {
      return
    }

    stop()
    timer.value = window.setInterval(run, config.interval)
  }

  const run = async (): Promise<void> => {
    const config = settings.value
    if (!config || isPaused.value) {
      return
    }

    try {
      const response = await fetchData()
      if (lastHash.value !== response.last_updated_at) {
        lastHash.value = response.last_updated_at
      }
    } catch {
      stop()
    }
  }

  const resetIdle = (): void => {
    const config = settings.value
    if (!config || !config.pause_on_idle) {
      return
    }
    isPaused.value = false

    if (idleTimer.value !== null) {
      window.clearTimeout(idleTimer.value)
    }

    idleTimer.value = window.setTimeout(() => {
      isPaused.value = true
    }, config.idle_timeout * 1000)
  }

  watch(
    settings,
    () => {
      stop()
      start()
    },
    { deep: true },
  )

  onMounted(() => {
    start()

    window.addEventListener('mousemove', resetIdle)
    window.addEventListener('keydown', resetIdle)
    resetIdle()
  })

  onUnmounted(() => {
    stop()

    if (idleTimer.value !== null) {
      window.clearTimeout(idleTimer.value)
    }

    window.removeEventListener('mousemove', resetIdle)
    window.removeEventListener('keydown', resetIdle)
  })

  return {
    start,
    stop,
  }
}
