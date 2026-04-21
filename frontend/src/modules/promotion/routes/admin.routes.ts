import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'discounts',
    name: 'admin.discounts',
    meta: {
      title: 'admin::sidebar.discounts',
      icon: 'tabler-shopping-bag-discount',
    },
    children: [
      {
        path: '',
        name: 'admin.discounts.index',
        component: () => import('@/modules/promotion/pages/admin/discount/Index.vue'),
        meta: {
          permission: 'admin.discounts.index',
        },
      },
      {
        path: 'create',
        name: 'admin.discounts.create',
        component: () => import('@/modules/promotion/pages/admin/discount/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'discount::discounts.discount' },
          permission: 'admin.discounts.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.discounts.edit',
        component: () => import('@/modules/promotion/pages/admin/discount/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'discount::discounts.discount' },
          permission: 'admin.discounts.edit',
        },
      },
    ],
  },
  {
    path: 'vouchers',
    name: 'admin.vouchers',
    meta: {
      title: 'admin::sidebar.vouchers',
      icon: 'tabler-ticket',
    },
    children: [
      {
        path: '',
        name: 'admin.vouchers.index',
        component: () => import('@/modules/promotion/pages/admin/voucher/Index.vue'),
        meta: {
          permission: 'admin.vouchers.index',
        },
      },
      {
        path: 'create',
        name: 'admin.vouchers.create',
        component: () => import('@/modules/promotion/pages/admin/voucher/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'voucher::vouchers.voucher' },
          permission: 'admin.vouchers.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.vouchers.edit',
        component: () => import('@/modules/promotion/pages/admin/voucher/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'voucher::vouchers.voucher' },
          permission: 'admin.vouchers.edit',
        },
      },
    ],
  },
]

export default adminRoutes
