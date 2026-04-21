import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'currency-rates',
    name: 'admin.currency_rates.index',
    component: () => import('@/modules/currency/pages/admin/currencyRate/Index.vue'),
    meta: {
      title: 'admin::sidebar.currency_rates',
      icon: 'ic-outline-currency-exchange',
      permission: 'admin.currency_rates.index',
    },
  },
]

export default adminRoutes
