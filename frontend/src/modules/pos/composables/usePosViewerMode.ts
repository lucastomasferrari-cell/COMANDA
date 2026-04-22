import { ref, watch } from 'vue'

export type PosViewerMode = 'tables' | 'quick'

const STORAGE_KEY = 'pos:viewer:mode'

function readInitial (): PosViewerMode {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY)
    if (raw === 'tables' || raw === 'quick') return raw
  } catch {
    // ignored: SSR / privacy mode / disabled storage
  }
  return 'tables'
}

const mode = ref<PosViewerMode>(readInitial())

watch(mode, value => {
  try {
    window.localStorage.setItem(STORAGE_KEY, value)
  } catch {
    // ignored
  }
})

export function usePosViewerMode () {
  function setMode (next: PosViewerMode) {
    mode.value = next
  }

  function toggle () {
    mode.value = mode.value === 'tables' ? 'quick' : 'tables'
  }

  return {
    mode,
    setMode,
    toggle,
  }
}
