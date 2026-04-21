export function formatPrice (amount: number | string, currency: string, precision = 3) {
  const formatted = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
    maximumFractionDigits: precision,
    minimumFractionDigits: precision,
  }).format(typeof amount === 'string' ? Number.parseFloat(amount) : amount)

  // Insert space between currency and number
  return formatted.replace(/([^\d\s]+)/, '$1 ')
}

export function formatCurrentDateForFileName () {
  const now = new Date()

  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')

  let hours = now.getHours()
  const minutes = String(now.getMinutes()).padStart(2, '0')
  const ampm = hours >= 12 ? 'pm' : 'am'

  hours = hours % 12
  hours = hours ? hours : 12
  const hourStr = String(hours).padStart(2, '0')

  return `${year}-${month}-${day}_${hourStr}-${minutes}-${ampm}`
}
