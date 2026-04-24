import type { AxiosError } from 'axios'
import type { Cart, UseCart } from '@/modules/cart/composables/cart.ts'
import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { useCart } from '@/modules/cart/composables/cart.ts'
import { getConfiguration, getMenuItems } from '@/modules/pos/api/posViewer.api.ts'
import { useQintrix } from '@/modules/printer/composables/qintrix.ts'

const defaultMeta: PosMeta = {
  branches: [],
  menus: [],
  registers: [],
  orderTypes: [],
  categories: [],
  products: [],
  waiters: [],
  customers: [],
  discounts: [],
  refundPaymentMethods: [],
  directions: [],
  reasons: {},
  order: null,
  currency: 'JOD',
}

const defaultForm: PosForm = {
  mode: 'create',
  branchId: null,
  menuId: null,
  registerId: null,
  waiter: null,
  sessionId: null,
  table: null,
  skipFirst: false,
  refundPaymentMethod: null,
  meta: {
    notes: null,
    guestCount: 1,
    carPlate: null,
    carDescription: null,
    scheduledAt: null,
  },
  loading: false,
  loadingMenuItems: false,
  reloadBranchData: false,
}

export function usePosViewer (cartId: string) {
  const qintrix = useQintrix()
  const cart: UseCart = useCart(cartId)

  const toast = useToast()
  const { t } = useI18n()

  const meta = ref<PosMeta>(structuredClone(defaultMeta))
  const form = ref<PosForm>(structuredClone(defaultForm))
  const skipNextMenuLoad = ref(false)

  // Flag explícito de intencion: solo es true cuando el user arranca una orden
  // (click "+ Nueva" o abre una existente desde ActiveOrdersPanel). Sin esto,
  // al entrar al POS el cart está en create mode vacio pero no queremos mostrar
  // el panel de orden aún — queremos empty state con CTA.
  const newOrderStarted = ref(false)

  const hasActiveOrder = computed(() => form.value.mode === 'edit' || newOrderStarted.value)

  const hasOpenSession = computed(() => meta.value.registers.length > 0 && form.value.sessionId != null)

  const hasConfigurationErrors = computed(() =>
    meta.value.branches.length === 0
    || meta.value.menus.length === 0
    || meta.value.registers.length === 0
    || !form.value.registerId
    || !form.value.sessionId,
  )

  watch(
    () => form.value.branchId,
    async (newValue, oldValue) => {
      if (oldValue && newValue !== oldValue) {
        await loadConfiguration((newValue as number))
      }
    })

  watch(
    () => form.value.registerId,
    async newValue => {
      const register = meta.value.registers.find((register: Record<string, any>) => register.id === newValue)
      form.value.sessionId = register && register?.session ? register.session.id : null
    })

  watch(
    () => form.value.menuId,
    async (newValue, oldValue) => {
      if (skipNextMenuLoad.value) {
        skipNextMenuLoad.value = false
        return
      }

      if (!hasConfigurationErrors.value && newValue && newValue !== oldValue) {
        await loadMenuItems()
      }
    })

  const loadConfiguration = async (branchId?: number) => {
    if (branchId) {
      form.value.reloadBranchData = true
    }
    form.value.loading = true
    try {
      const response = (await getConfiguration(cartId, branchId)).data.body
      if (!branchId) {
        meta.value.branches = response.branches
        meta.value.customers = response.customers
        meta.value.directions = response.directions
        meta.value.reasons = response.reasons
      }

      meta.value.waiters = response.waiters
      meta.value.orderTypes = response.order_types
      meta.value.menus = response.menus
      meta.value.discounts = response.discounts
      meta.value.registers = response.registers
      meta.value.currency = response.currency
      meta.value.categories = response.menu_items?.categories || []
      meta.value.products = response.menu_items?.products || []
      // Sprint 3.A — feature_flags para el switcher de modos.
      if (response.feature_flags) {
        (meta.value as any).feature_flags = response.feature_flags
      }
      cart.resetCart(response.cart)
      skipNextMenuLoad.value = true
      form.value.branchId = response.branch_id
      form.value.menuId = response.menu_id
      form.value.registerId = response.register_id
      form.value.sessionId = response.session_id
      if (response.agent) {
        qintrix.initClient(response.agent.base_url, response.agent.api_key)
      }
    } catch (error: any) {
      toast.error((error as AxiosError<{
        message?: string
      }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
    } finally {
      form.value.loading = false
      form.value.reloadBranchData = false
    }
  }

  const loadMenuItems = async () => {
    if (!form.value.menuId) {
      return
    }
    form.value.loadingMenuItems = true
    try {
      const response = (await getMenuItems(cartId, form.value.menuId)).data.body
      meta.value.products = response.products
      meta.value.categories = response.categories
    } catch (error: any) {
      toast.error((error as AxiosError<{
        message?: string
      }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
    } finally {
      form.value.loadingMenuItems = false
    }
  }

  const initOrder = (response: Record<string, any>) => {
    cart.resetCart(response.cart)
    meta.value.order = response.order
    meta.value.refundPaymentMethods = response.refund_payment_methods

    form.value = { ...form.value, ...response.form, mode: 'edit', skipFirst: true }
  }

  const reset = (cartData?: Cart) => {
    if (cartData) {
      cart.resetCart(cartData)
    }
    form.value = {
      ...form.value,
      meta: { ...structuredClone(defaultForm).meta },
      mode: 'create',
      waiter: null,
      table: null,
    }
    newOrderStarted.value = false
  }

  // Arranca una orden nueva en create mode. Prende el flag enseguida para que
  // la UI mute el empty state sin esperar al backend; si cart.clear falla,
  // revertimos. El overload con opts se usa desde el plano visual cuando el
  // user clickea una mesa libre (pre-setea table + guestCount + orderType).
  const startNewOrder = async (opts?: { table?: Record<string, any> | null, guestCount?: number }) => {
    if (cart.processing.value) return
    newOrderStarted.value = true
    try {
      await cart.clear()
      if (opts?.table) {
        await cart.storeOrderType("dine_in")
      }
      form.value = {
        ...form.value,
        meta: {
          ...structuredClone(defaultForm).meta,
          guestCount: opts?.guestCount ?? 1,
        },
        mode: 'create',
        waiter: null,
        table: opts?.table ?? null,
      }
    } catch {
      newOrderStarted.value = false
    }
  }

  return {
    cart,
    reset,
    initOrder,
    loadConfiguration,
    loadMenuItems,
    hasConfigurationErrors,
    hasOpenSession,
    hasActiveOrder,
    startNewOrder,
    meta,
    qintrix,
    form,
  }
}
