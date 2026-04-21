import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'inventories/analytics',
    name: 'admin.inventories.analytics',
    component: () => import('@/modules/inventory/pages/admin/analytics/Index.vue'),
    meta: {
      title: 'admin::sidebar.inventory_analytics',
      icon: 'tabler-brand-google-analytics',
      permission: 'admin.inventories.analytics',
    },
  },
  {
    path: 'units',
    name: 'admin.units',
    meta: {
      title: 'admin::sidebar.units',
      icon: 'tabler-ruler',
    },
    children: [
      {
        path: '',
        name: 'admin.units.index',
        component: () => import('@/modules/inventory/pages/admin/unit/Index.vue'),
        meta: {
          permission: 'admin.units.index',
        },
      },
      {
        path: 'create',
        name: 'admin.units.create',
        component: () => import('@/modules/inventory/pages/admin/unit/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'inventory::units.unit' },
          permission: 'admin.units.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.units.edit',
        component: () => import('@/modules/inventory/pages/admin/unit/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'inventory::units.unit' },
          permission: 'admin.units.edit',
        },
      },
    ],
  },
  {
    path: 'suppliers',
    name: 'admin.suppliers',
    meta: {
      title: 'admin::sidebar.suppliers',
      icon: 'tabler-truck-loading',
    },
    children: [
      {
        path: '',
        name: 'admin.suppliers.index',
        component: () => import('@/modules/inventory/pages/admin/supplier/Index.vue'),
        meta: {
          permission: 'admin.suppliers.index',
        },
      },
      {
        path: 'create',
        name: 'admin.suppliers.create',
        component: () => import('@/modules/inventory/pages/admin/supplier/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'inventory::suppliers.supplier' },
          permission: 'admin.suppliers.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.suppliers.edit',
        component: () => import('@/modules/inventory/pages/admin/supplier/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'inventory::suppliers.supplier' },
          permission: 'admin.suppliers.edit',
        },
      },
    ],
  },
  {
    path: 'ingredients',
    name: 'admin.ingredients',
    meta: {
      title: 'admin::sidebar.ingredients',
      icon: 'ic-outline-fastfood',
    },
    children: [
      {
        path: '',
        name: 'admin.ingredients.index',
        component: () => import('@/modules/inventory/pages/admin/ingredient/Index.vue'),
        meta: {
          permission: 'admin.ingredients.index',
        },
      },
      {
        path: 'create',
        name: 'admin.ingredients.create',
        component: () => import('@/modules/inventory/pages/admin/ingredient/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'inventory::ingredients.ingredient' },
          permission: 'admin.ingredients.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.ingredients.edit',
        component: () => import('@/modules/inventory/pages/admin/ingredient/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'inventory::ingredients.ingredient' },
          permission: 'admin.ingredients.edit',
        },
      },
    ],
  },
  {
    path: 'stock-movements',
    name: 'admin.stock_movements',
    meta: {
      title: 'admin::sidebar.stock_movements',
      icon: 'tabler-chart-histogram',
    },
    children: [
      {
        path: '',
        name: 'admin.stock_movements.index',
        component: () => import('@/modules/inventory/pages/admin/stockMovement/Index.vue'),
        meta: {
          permission: 'admin.stock_movements.index',
        },
      },
      {
        path: 'create',
        name: 'admin.stock_movements.create',
        component: () => import('@/modules/inventory/pages/admin/stockMovement/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'inventory::stock_movements.stock_movement' },
          permission: 'admin.stock_movements.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.stock_movements.edit',
        component: () => import('@/modules/inventory/pages/admin/stockMovement/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'inventory::stock_movements.stock_movement' },
          permission: 'admin.stock_movements.edit',
        },
      },
      {
        path: ':id/show',
        name: 'admin.stock_movements.show',
        component: () => import('@/modules/inventory/pages/admin/stockMovement/Show.vue'),
        meta: {
          title: 'admin::resource.show',
          transParam: { resource: 'inventory::stock_movements.stock_movement' },
          permission: 'admin.stock_movements.show',
        },
      },
    ],
  },
  {
    path: 'purchases',
    name: 'admin.purchases',
    meta: {
      title: 'admin::sidebar.purchases',
      icon: 'tabler-report-money',
    },
    children: [
      {
        path: '',
        name: 'admin.purchases.index',
        component: () => import('@/modules/inventory/pages/admin/purchase/Index.vue'),
        meta: {
          permission: 'admin.purchases.index',
        },
      },
      {
        path: 'create',
        name: 'admin.purchases.create',
        component: () => import('@/modules/inventory/pages/admin/purchase/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'inventory::purchases.purchase' },
          permission: 'admin.purchases.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.purchases.edit',
        component: () => import('@/modules/inventory/pages/admin/purchase/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'inventory::purchases.purchase' },
          permission: 'admin.purchases.edit',
        },
      },
      {
        path: ':id/show',
        name: 'admin.purchases.show',
        component: () => import('@/modules/inventory/pages/admin/purchase/Show.vue'),
        meta: {
          title: 'admin::resource.show',
          transParam: { resource: 'inventory::purchases.purchase' },
          permission: 'admin.purchases.show',
        },
      },
    ],
  },
]

export default adminRoutes
