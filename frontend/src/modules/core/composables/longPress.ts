import type { MaybeRef } from 'vue'
import { onBeforeUnmount, unref, watch } from 'vue'

interface LongPressOptions {
  /**
   * Duración en ms que hay que mantener el pointer antes de disparar
   * el handler. Default 500 — Toast usa ese valor, mismo que Android.
   */
  delay?: number
}

/**
 * Firma drop-in compatible con `useLongPress` de @vueuse/core. Cuando
 * eventualmente agreguemos @vueuse al proyecto podemos reemplazar el
 * import sin tocar los call-sites.
 *
 * Uso:
 *   const el = ref<HTMLElement>()
 *   useLongPress(el, () => openQuickActions(), { delay: 500 })
 *
 * Funciona con mouse (mousedown/up/leave) y touch (touchstart/end/cancel).
 * Movimiento mayor a 10px cancela (para que scrollear no dispare).
 */
export function useLongPress (
  target: MaybeRef<HTMLElement | null | undefined>,
  handler: (e: PointerEvent | MouseEvent | TouchEvent) => void,
  options: LongPressOptions = {},
): void {
  const delay = options.delay ?? 500
  let timer: number | null = null
  let startX = 0
  let startY = 0
  let currentEl: HTMLElement | null = null

  const clear = (): void => {
    if (timer !== null) {
      window.clearTimeout(timer)
      timer = null
    }
  }

  const onStart = (e: MouseEvent | TouchEvent): void => {
    const point = 'touches' in e ? e.touches[0] : e
    if (!point) return
    startX = point.clientX
    startY = point.clientY
    timer = window.setTimeout(() => {
      handler(e)
      clear()
    }, delay)
  }

  const onMove = (e: MouseEvent | TouchEvent): void => {
    if (timer === null) return
    const point = 'touches' in e ? e.touches[0] : e
    if (!point) return
    // Cancelar si el dedo/mouse se mueve más de 10px (el user está
    // scrolleando o draggeando, no tapeando).
    if (Math.abs(point.clientX - startX) > 10 || Math.abs(point.clientY - startY) > 10) {
      clear()
    }
  }

  const attach = (el: HTMLElement): void => {
    el.addEventListener('mousedown', onStart)
    el.addEventListener('mouseup', clear)
    el.addEventListener('mouseleave', clear)
    el.addEventListener('touchstart', onStart, { passive: true })
    el.addEventListener('touchmove', onMove, { passive: true })
    el.addEventListener('touchend', clear)
    el.addEventListener('touchcancel', clear)
    el.addEventListener('contextmenu', e => e.preventDefault())
  }

  const detach = (el: HTMLElement): void => {
    el.removeEventListener('mousedown', onStart)
    el.removeEventListener('mouseup', clear)
    el.removeEventListener('mouseleave', clear)
    el.removeEventListener('touchstart', onStart)
    el.removeEventListener('touchmove', onMove)
    el.removeEventListener('touchend', clear)
    el.removeEventListener('touchcancel', clear)
  }

  watch(
    () => unref(target),
    (newEl, oldEl) => {
      if (oldEl) detach(oldEl)
      if (newEl) {
        currentEl = newEl
        attach(newEl)
      }
    },
    { immediate: true },
  )

  onBeforeUnmount(() => {
    clear()
    if (currentEl) detach(currentEl)
  })
}
