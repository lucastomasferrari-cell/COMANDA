export function getOnlineMenuUrl (slug: string) {
  return `${window.origin}/online-menu/${slug}`
}

/**
 * Generate a random voucher code
 *
 * @param {Object} options
 * @param {String} [options.prefix] - Optional prefix like "FORKIVA"
 * @param {Number} [options.length] - Number of random characters to append
 * @param {Boolean} [options.includeYear] - Append current year at the end
 * @returns {String}
 */
export function generateVoucherCode ({
  prefix = '',
  length = 6,
  includeYear = false,
} = {}) {
  const chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'
  let randomPart = ''

  for (let i = 0; i < length; i++) {
    randomPart += chars.charAt(Math.floor(Math.random() * chars.length))
  }

  let code = prefix ? `${prefix.toUpperCase()}-${randomPart}` : randomPart

  if (includeYear) {
    const year = new Date().getFullYear()
    code = `${code}${year}`
  }

  return code
}

export function isUUID (str: string): boolean {
  const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i
  return uuidRegex.test(str)
}

export function formatQty (q: any): string {
  if (!q) {
    return '0'
  }
  const n = Number(q)
  return Number.isInteger(n) ? n.toString() : n.toFixed(2)
}
