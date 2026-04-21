import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'taxes',
    name: 'admin.taxes',
    meta: {
      title: 'admin::sidebar.taxes',
      icon: 'tabler-tax',
    },
    children: [
      {
        path: '',
        name: 'admin.taxes.index',
        component: () => import('@/modules/tax/pages/admin/tax/Index.vue'),
        meta: {
          permission: 'admin.taxes.index',
        },
      },
      {
        path: 'create',
        name: 'admin.taxes.create',
        component: () => import('@/modules/tax/pages/admin/tax/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'tax::taxes.tax' },
          permission: 'admin.taxes.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.taxes.edit',
        component: () => import('@/modules/tax/pages/admin/tax/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'tax::taxes.tax' },
          permission: 'admin.taxes.edit',
        },
      },
    ],
  },
]

export default adminRoutes
