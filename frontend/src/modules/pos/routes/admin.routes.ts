import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'pos/viewer/:cartId',
    name: 'admin.pos_viewer',
    meta: {
      title: 'pos::pos_viewer.pos_viewer',
      icon: 'tabler-cash-register',
      pageHeader: false,
      hiddenSidebar: true,
    },
    children: [
      {
        path: '',
        name: 'admin.pos.index',
        component: () => import('@/modules/pos/pages/admin/viewer/Index.vue'),
        meta: {
          permission: 'admin.pos.index',
        },
      },
    ],
  },
  {
    path: 'pos/customer/viewer/:cartId',
    name: 'admin.pos.customer_viewer',
    meta: {
      title: 'pos::pos_viewer.customer_viewer',
      icon: 'tabler-eye',
      pageHeader: false,
      hiddenSidebar: true,
      hiddenTopHeader: true,
    },
    children: [
      {
        path: '',
        name: 'admin.pos_viewer.customer_viewer',
        component: () => import('@/modules/pos/pages/admin/Customer/Index.vue'),
        meta: {
          permission: 'admin.pos.index',
        },
      },
    ],
  },
  {
    path: 'pos/kitchen-viewer',
    name: 'admin.pos_kitchen_viewer',
    meta: {
      title: 'pos::pos_viewer.kitchen_viewer',
      icon: 'tabler-chef-hat',
      pageHeader: false,
      hiddenSidebar: true,
    },
    children: [
      {
        path: '',
        name: 'admin.pos.kitchen_viewer',
        component: () => import('@/modules/pos/pages/admin/kitchenViewer/Index.vue'),
        meta: {
          permission: 'admin.pos.kitchen_viewer',
        },
      },
    ],
  },
  {
    path: 'pos/registers',
    name: 'admin.pos_registers',
    meta: {
      title: 'pos::pos_registers.pos_registers',
      icon: 'tabler-device-desktop-cog',
    },
    children: [
      {
        path: '',
        name: 'admin.pos_registers.index',
        component: () => import('@/modules/pos/pages/admin/register/Index.vue'),
        meta: {
          permission: 'admin.pos_registers.index',
        },
      },
      {
        path: 'create',
        name: 'admin.pos_registers.create',
        component: () => import('@/modules/pos/pages/admin/register/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'pos::pos_registers.pos_register' },
          permission: 'admin.pos_registers.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.pos_registers.edit',
        component: () => import('@/modules/pos/pages/admin/register/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'pos::pos_registers.pos_register' },
          permission: 'admin.pos_registers.edit',
        },
      },
    ],
  },
  {
    path: 'pos/sessions',
    name: 'admin.pos_sessions',
    meta: {
      title: 'pos::pos_sessions.pos_sessions',
      icon: 'tabler-clock-dollar',
    },
    children: [
      {
        path: '',
        name: 'admin.pos_sessions.index',
        component: () => import('@/modules/pos/pages/admin/session/Index.vue'),
        meta: {
          permission: 'admin.pos_sessions.index',
        },
      },
      {
        path: ':id/cash-movements',
        name: 'admin.pos_sessions.pos_cash_movements.index',
        component: () => import('@/modules/pos/pages/admin/cashMovement/Index.vue'),
        meta: {
          title: 'pos::pos_cash_movements.pos_cash_movements',
          permission: 'admin.pos_cash_movements.index',
          icon: 'tabler-cash',
        },
      },
    ],
  },
  {
    path: 'pos/cash-movements',
    name: 'admin.pos_cash_movements',
    meta: {
      title: 'pos::pos_cash_movements.pos_cash_movements',
      icon: 'tabler-cash',
    },
    children: [
      {
        path: '',
        name: 'admin.pos_cash_movements.index',
        component: () => import('@/modules/pos/pages/admin/cashMovement/Index.vue'),
        meta: {
          permission: 'admin.pos_cash_movements.index',
        },
      },
    ],
  },
]

export default adminRoutes
