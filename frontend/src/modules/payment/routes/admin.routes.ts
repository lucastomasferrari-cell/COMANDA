import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'payment-methods',
    name: 'admin.payment_methods',
    children: [
      {
        path: '',
        name: 'admin.payment_methods.index',
        // Redirect al tab del hub Cobros. El listado vive ahí post-Fix 8.
        redirect: { name: 'admin.cobros.formas' },
      },
      {
        path: 'create',
        name: 'admin.payment_methods.create',
        component: () => import('@/modules/payment/pages/admin/paymentMethod/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'payment::payment_methods.payment_method' },
          permission: 'admin.payment_methods.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.payment_methods.edit',
        component: () => import('@/modules/payment/pages/admin/paymentMethod/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'payment::payment_methods.payment_method' },
          permission: 'admin.payment_methods.edit',
        },
      },
    ],
  },
]

export default adminRoutes
