import type {Directive} from 'vue'

/**
 * Vue directive: v-integer-en
 *
 * Allows only English digits (0–9).
 * Rejects Arabic, Persian, and any other non-ASCII numerals or characters.
 *
 * Example:
 *   <VTextField v-model="form.value" v-integer-en />
 */
const integerEn: Directive<HTMLInputElement> = {
  beforeMount(el: HTMLInputElement) {
    el.addEventListener('input', (e: Event) => {
      const target = e.target as HTMLInputElement | null
      if (!target) {
        return
      }

      let value: string = target.value || ''

      // ✅ 1. Keep ONLY ASCII digits (U+0030–U+0039)
      //     This excludes Arabic (U+0660–U+0669) and Persian (U+06F0–U+06F9)
      value = value.replace(/[^0-9]/g, '')

      // ✅ 2. Explicitly filter out any non-ASCII numerals
      //     Ensures invisible or RTL numerals are removed
      value = [...value]
        .filter(ch => {
          const code = ch.codePointAt(0) ?? 0
          return code >= 48 && code <= 57
        })
        .join('')

      // ✅ 3. Update only if changed to avoid redundant input events
      if (target.value !== value) {
        target.value = value
        target.dispatchEvent(new Event('input', {bubbles: true}))
      }
    })
  },
}

export default integerEn
