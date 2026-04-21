import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'orders',
    name: 'admin.orders',
    meta: {
      title: 'admin::sidebar.orders',
      icon: 'tabler-salad',
    },
    children: [
      {
        path: '',
        name: 'admin.orders.index',
        component: () => import('@/modules/sale/pages/admin/order/Index.vue'),
        meta: {
          permission: 'admin.orders.index',
        },
      },
      {
        path: ':id/show',
        name: 'admin.orders.show',
        component: () => import('@/modules/sale/pages/admin/order/Show.vue'),
        meta: {
          title: 'admin::resource.show',
          transParam: { resource: 'order::orders.order' },
          permission: 'admin.orders.show',
        },
      },
    ],
  },
  {
    path: 'invoices',
    name: 'admin.invoices',
    meta: {
      title: 'admin::sidebar.invoices',
      icon: 'tabler-file-invoice',
    },
    children: [
      {
        path: '',
        name: 'admin.invoices.index',
        component: () => import('@/modules/sale/pages/admin/invoice/Index.vue'),
        meta: {
          permission: 'admin.invoices.index',
        },
      },
      {
        path: ':id/show',
        name: 'admin.invoices.show',
        component: () => import('@/modules/sale/pages/admin/invoice/Show.vue'),
        meta: {
          title: 'admin::resource.show',
          transParam: { resource: 'invoice::invoices.invoice' },
          permission: 'admin.invoices.show',
        },
      },
    ],
  },
  {
    path: 'payments',
    name: 'admin.payments',
    meta: {
      title: 'admin::sidebar.payments',
      icon: 'tabler-receipt-dollar',
    },
    children: [
      {
        path: '',
        name: 'admin.payments.index',
        component: () => import('@/modules/sale/pages/admin/payment/Index.vue'),
        meta: {
          permission: 'admin.payments.index',
        },
      },
    ],
  },
  {
    path: 'reasons',
    name: 'admin.reasons',
    meta: {
      title: 'admin::sidebar.reasons',
      icon: 'tabler-ban',
    },
    children: [
      {
        path: '',
        name: 'admin.reasons.index',
        component: () => import('@/modules/sale/pages/admin/reason/Index.vue'),
        meta: {
          permission: 'admin.reasons.index',
        },
      },
      {
        path: 'create',
        name: 'admin.reasons.create',
        component: () => import('@/modules/sale/pages/admin/reason/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'order::reasons.reason' },
          permission: 'admin.reasons.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.reasons.edit',
        component: () => import('@/modules/sale/pages/admin/reason/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'order::reasons.reason' },
          permission: 'admin.reasons.edit',
        },
      },
    ],
  },
]

export default adminRoutes
