import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'menus',
    name: 'admin.menus',
    meta: {
      title: 'menu::menus.menus',
      icon: 'tabler-list-details',
    },
    children: [
      {
        path: '',
        name: 'admin.menus.index',
        component: () => import('@/modules/menu/pages/admin/menu/Index.vue'),
        meta: {
          permission: 'admin.menus.index',
        },
      },
      {
        path: 'create',
        name: 'admin.menus.create',
        component: () => import('@/modules/menu/pages/admin/menu/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'menu::menus.menu' },
          permission: 'admin.menus.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.menus.edit',
        component: () => import('@/modules/menu/pages/admin/menu/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'menu::menus.menu' },
          permission: 'admin.menus.edit',
        },
      },
      {
        path: ':id/categories',
        name: 'admin.menus.categories',
        component: () => import('@/modules/menu/pages/admin/category/Index.vue'),
        meta: {
          title: 'admin::sidebar.categories',
          icon: 'tabler-folders',
          permission: 'admin.categories.index',
        },
      },
      {
        path: ':menuId/products',
        name: 'admin.menus.products',
        meta: {
          title: 'admin::sidebar.products',
          icon: 'tabler-package',
        },
        children: [
          {
            path: '',
            name: 'admin.menus.products.index',
            component: () => import('@/modules/menu/pages/admin/product/Index.vue'),
            meta: {
              permission: 'admin.products.index',
            },
          },
          {
            path: 'create',
            name: 'admin.menus.products.create',
            component: () => import('@/modules/menu/pages/admin/product/Create.vue'),
            meta: {
              title: 'admin::resource.create',
              transParam: { resource: 'product::products.product' },
              permission: 'admin.products.create',
            },
          },
          {
            path: ':id/edit',
            name: 'admin.menus.products.edit',
            component: () => import('@/modules/menu/pages/admin/product/Edit.vue'),
            meta: {
              title: 'admin::resource.edit',
              transParam: { resource: 'product::products.product' },
              permission: 'admin.products.edit',
            },
          },
        ],
      },
    ],
  },
  {
    path: 'online-menus',
    name: 'admin.online_menus',
    meta: {
      title: 'menu::online_menus.online_menus',
      icon: 'tabler-link',
    },
    children: [
      {
        path: '',
        name: 'admin.online_menus.index',
        component: () => import('@/modules/menu/pages/admin/onlineMenu/Index.vue'),
        meta: {
          permission: 'admin.online_menus.index',
        },
      },
      {
        path: 'create',
        name: 'admin.online_menus.create',
        component: () => import('@/modules/menu/pages/admin/onlineMenu/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'menu::online_menus.online_menu' },
          permission: 'admin.online_menus.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.online_menus.edit',
        component: () => import('@/modules/menu/pages/admin/onlineMenu/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'menu::online_menus.online_menu' },
          permission: 'admin.online_menus.edit',
        },
      },
    ],
  },
  {
    path: 'categories',
    name: 'admin.categories',
    meta: {
      title: 'admin::sidebar.categories',
      icon: 'tabler-folders',
    },
    children: [
      {
        path: '',
        name: 'admin.categories.index',
        component: () => import('@/modules/menu/pages/admin/category/Index.vue'),
        meta: {
          permission: 'admin.categories.index',
        },
      },
    ],
  },
  {
    path: 'products',
    name: 'admin.products',
    meta: {
      title: 'admin::sidebar.products',
      icon: 'tabler-package',
    },
    children: [
      {
        path: '',
        name: 'admin.products.index',
        component: () => import('@/modules/menu/pages/admin/product/Index.vue'),
        meta: {
          permission: 'admin.products.index',
        },
      },
      {
        path: 'create',
        name: 'admin.products.create',
        component: () => import('@/modules/menu/pages/admin/product/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'product::products.product' },
          permission: 'admin.products.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.products.edit',
        component: () => import('@/modules/menu/pages/admin/product/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'product::products.product' },
          permission: 'admin.products.edit',
        },
      },
    ],
  },
  {
    path: 'options',
    name: 'admin.options',
    meta: {
      title: 'admin::sidebar.options',
      icon: 'tabler-adjustments',
    },
    children: [
      {
        path: '',
        name: 'admin.options.index',
        component: () => import('@/modules/menu/pages/admin/option/Index.vue'),
        meta: {
          permission: 'admin.options.index',
        },
      },
      {
        path: 'create',
        name: 'admin.options.create',
        component: () => import('@/modules/menu/pages/admin/option/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'option::options.option' },
          permission: 'admin.options.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.options.edit',
        component: () => import('@/modules/menu/pages/admin/option/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'option::options.option' },
          permission: 'admin.options.edit',
        },
      },
    ],
  },
]

export default adminRoutes
