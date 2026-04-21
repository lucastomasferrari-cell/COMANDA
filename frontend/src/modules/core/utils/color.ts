export function convertHexToRgba (hexColor: string, alpha = 0.2) {
  const bigint = Number.parseInt(hexColor.slice(1), 16)
  const r = (bigint >> 16) & 255
  const g = (bigint >> 8) & 255
  const b = bigint & 255
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}
