import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'users',
    name: 'admin.users',
    meta: {
      title: 'admin::sidebar.users',
      icon: 'tabler-users',
    },
    children: [
      {
        path: '',
        name: 'admin.users.index',
        component: () => import('@/modules/user/pages/admin/user/Index.vue'),
        meta: {
          permission: 'admin.users.index',
        },
      },
      {
        path: 'create',
        name: 'admin.users.create',
        component: () => import('@/modules/user/pages/admin/user/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'user::users.user' },
          permission: 'admin.users.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.users.edit',
        component: () => import('@/modules/user/pages/admin/user/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'user::users.user' },
          permission: 'admin.users.edit',
        },
      },
    ],
  },
  {
    path: 'customers',
    name: 'admin.customers',
    meta: {
      title: 'admin::sidebar.customers',
      icon: 'tabler-users-group',
    },
    children: [
      {
        path: '',
        name: 'admin.customers.index',
        component: () => import('@/modules/user/pages/admin/customer/Index.vue'),
        meta: {
          permission: 'admin.customers.index',
        },
      },
      {
        path: 'create',
        name: 'admin.customers.create',
        component: () => import('@/modules/user/pages/admin/customer/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'user::customers.customer' },
          permission: 'admin.customers.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.customers.edit',
        component: () => import('@/modules/user/pages/admin/customer/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'user::customers.customer' },
          permission: 'admin.customers.edit',
        },
      },
    ],
  },
  {
    path: 'roles',
    name: 'admin.roles',
    meta: {
      title: 'admin::sidebar.roles',
      icon: 'tabler-user-shield',
    },
    children: [
      {
        path: '',
        name: 'admin.roles.index',
        component: () => import('@/modules/user/pages/admin/role/Index.vue'),
        meta: {
          permission: 'admin.roles.index',
        },
      },
      {
        path: 'create',
        name: 'admin.roles.create',
        component: () => import('@/modules/user/pages/admin/role/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'user::roles.role' },
          permission: 'admin.roles.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.roles.edit',
        component: () => import('@/modules/user/pages/admin/role/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'user::roles.role' },
          permission: 'admin.roles.edit',
        },
      },
    ],
  },
]

export default adminRoutes
