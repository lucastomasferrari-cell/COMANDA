import { getConfiguration, getOrders, moveToNextStatus } from '@/modules/pos/api/kitchenViewer.api.ts'

export function useKitchenViewer () {
  return { getOrders, getConfiguration, moveToNextStatus }
}
