import type { RouteRecordRaw } from 'vue-router'

// Las rutas standalone de Products/Options/Categories quedaron del vendor
// original con 4 árboles paralelos a /admin/menus/. Post-refactor Fix 3
// parte E, el flujo canónico es /admin/menu/<tab>/create|edit (hub visible
// con tabs). Estas rutas se convierten en redirects para preservar cualquier
// link viejo (emails, bookmarks, copy-paste de URL del usuario).
//
// Lo que sí conserva lógica propia: /admin/menus (CRUD de menús standalone
// con drilldown a productos de un menú específico) y /admin/online-menus.

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
        redirect: { name: 'admin.menu.menus' },
      },
      {
        path: 'create',
        name: 'admin.menus.create',
        redirect: { name: 'admin.menu.menus.create' },
      },
      {
        path: ':id/edit',
        name: 'admin.menus.edit',
        redirect: to => ({ name: 'admin.menu.menus.edit', params: { id: to.params.id } }),
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
        // Drilldown: productos de un menú específico. Preserva el flujo
        // original del vendor (TableViewer del menú → crear producto
        // con ese menú pre-seleccionado). No se redirige al hub porque
        // el menuId sería ambiguo y el hub no soporta drilldown per-menu.
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
    children: [
      {
        path: '',
        name: 'admin.categories.index',
        redirect: { name: 'admin.menu.categorias' },
      },
    ],
  },
  {
    path: 'products',
    name: 'admin.products',
    children: [
      {
        path: '',
        name: 'admin.products.index',
        redirect: { name: 'admin.menu.productos' },
      },
      {
        path: 'create',
        name: 'admin.products.create',
        redirect: { name: 'admin.menu.productos.create' },
      },
      {
        path: ':id/edit',
        name: 'admin.products.edit',
        redirect: to => ({ name: 'admin.menu.productos.edit', params: { id: to.params.id } }),
      },
    ],
  },
  {
    path: 'options',
    name: 'admin.options',
    children: [
      {
        path: '',
        name: 'admin.options.index',
        redirect: { name: 'admin.menu.opciones' },
      },
      {
        path: 'create',
        name: 'admin.options.create',
        redirect: { name: 'admin.menu.opciones.create' },
      },
      {
        path: ':id/edit',
        name: 'admin.options.edit',
        redirect: to => ({ name: 'admin.menu.opciones.edit', params: { id: to.params.id } }),
      },
    ],
  },
]

export default adminRoutes
