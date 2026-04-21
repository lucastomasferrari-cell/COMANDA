import type {Directive} from 'vue'

/**
 * Vue directive: v-decimal-en
 *
 * Allows only English digits (0–9) and a single decimal point (.).
 * Rejects Arabic, Persian, full-width, or any other non-ASCII numerals.
 * Example:
 *   <VTextField v-model="form.value" v-decimal-en />
 */
const decimalEn: Directive<HTMLInputElement> = {
  beforeMount(el: HTMLInputElement) {
    el.addEventListener('input', (e: Event) => {
      const target = e.target as HTMLInputElement | null
      if (!target) {
        return
      }

      let value: string = target.value || ''

      // ✅ 1. Keep only ASCII digits (U+0030–U+0039) and dot (.)
      //     This automatically rejects Arabic, Persian, full-width, etc.
      value = value
        .replace(/[^0-9.]/g, '') // remove non-English numerals & letters
        .replace(/(\..*)\./g, '$1') // allow only one dot

      // ✅ 2. Prevent leading dot → ".5" → "0.5"
      if (value.startsWith('.')) {
        value = '0' + value
      }

      // ✅ 3. Remove non-ASCII numerals that may sneak in via copy/paste
      value = [...value]
        .filter(ch => {
          const code = ch.codePointAt(0) ?? 0
          return (code >= 48 && code <= 57) || code === 46 // digits or '.'
        })
        .join('')

      // ✅ 4. Limit to 4 decimal places (customizable)
      value = value.replace(/^(\d+)(\.\d{0,4})?.*$/, '$1$2')

      // ✅ 5. Update only if changed to prevent redundant reactivity
      if (target.value !== value) {
        target.value = value
        target.dispatchEvent(new Event('input', {bubbles: true}))
      }
    })
  },
}

export default decimalEn
