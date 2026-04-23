import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: '',
    name: 'admin.dashboard',
    component: () => import('@/modules/admin/pages/admin/dashboard/Index.vue'),
    meta: {
      title: 'admin::sidebar.dashboard',
      pageHeader: false,
    },
  },

  // Menu hub — 4 tabs. Cada tab ahora tiene sub-rutas create/edit
  // anidadas para que el hub (con las tabs visibles) se mantenga al
  // editar. El router-view de PageTabs renderiza el Form.vue en vez
  // del Index.vue cuando la ruta es create/:id/edit. VTabs de Vuetify
  // detecta activo por prefijo de path, así que la tab sigue highlight.
  // Las rutas standalone (admin.products.*, admin.options.*, etc.)
  // viven todavía en modules/menu/routes/admin.routes.ts pero ahora
  // solo como redirects a estas anidadas — ver ese archivo.
  {
    path: 'menu',
    component: () => import('@/modules/admin/pages/admin/hubs/MenuHub.vue'),
    meta: { title: 'admin::sidebar.menu' },
    children: [
      { path: '', redirect: { name: 'admin.menu.menus' } },

      // Tab Menús (default)
      {
        path: 'menus',
        name: 'admin.menu.menus',
        component: () => import('@/modules/menu/pages/admin/menu/Index.vue'),
        meta: { permission: 'admin.menus.index' },
      },
      {
        path: 'menus/create',
        name: 'admin.menu.menus.create',
        component: () => import('@/modules/menu/pages/admin/menu/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'menu::menus.menu' },
          permission: 'admin.menus.create',
        },
      },
      {
        path: 'menus/:id/edit',
        name: 'admin.menu.menus.edit',
        component: () => import('@/modules/menu/pages/admin/menu/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'menu::menus.menu' },
          permission: 'admin.menus.edit',
        },
      },

      // Tab Categorías
      {
        path: 'categorias',
        name: 'admin.menu.categorias',
        component: () => import('@/modules/menu/pages/admin/category/Index.vue'),
        meta: { permission: 'admin.categories.index' },
      },

      // Tab Opciones
      {
        path: 'opciones',
        name: 'admin.menu.opciones',
        component: () => import('@/modules/menu/pages/admin/option/Index.vue'),
        meta: { permission: 'admin.options.index' },
      },
      {
        path: 'opciones/create',
        name: 'admin.menu.opciones.create',
        component: () => import('@/modules/menu/pages/admin/option/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'option::options.option' },
          permission: 'admin.options.create',
        },
      },
      {
        path: 'opciones/:id/edit',
        name: 'admin.menu.opciones.edit',
        component: () => import('@/modules/menu/pages/admin/option/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'option::options.option' },
          permission: 'admin.options.edit',
        },
      },

      // Tab Productos
      {
        path: 'productos',
        name: 'admin.menu.productos',
        component: () => import('@/modules/menu/pages/admin/product/Index.vue'),
        meta: { permission: 'admin.products.index' },
      },
      {
        path: 'productos/create',
        name: 'admin.menu.productos.create',
        component: () => import('@/modules/menu/pages/admin/product/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'product::products.product' },
          permission: 'admin.products.create',
        },
      },
      {
        path: 'productos/:id/edit',
        name: 'admin.menu.productos.edit',
        component: () => import('@/modules/menu/pages/admin/product/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'product::products.product' },
          permission: 'admin.products.edit',
        },
      },
    ],
  },

  // Salon hub — plano integral (Floors+Zones+Tables stackeados) + 2 placeholders.
  {
    path: 'salon',
    component: () => import('@/modules/admin/pages/admin/hubs/SalonHub.vue'),
    meta: { title: 'admin::sidebar.salon' },
    children: [
      { path: '', redirect: { name: 'admin.salon.plano' } },
      {
        path: 'plano-de-mesas',
        name: 'admin.salon.plano',
        component: () => import('@/modules/seatingPlan/pages/admin/SalonPlanoView.vue'),
        meta: { permission: 'admin.tables.index' },
      },
      {
        path: 'canales-de-venta',
        name: 'admin.salon.canales',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
    ],
  },

  // Alias corto: /admin/kds → full-screen KDS viewer (admin.pos.kitchen_viewer).
  // La ruta full-screen vive en modules/pos/routes/admin.routes.ts (/admin/pos/kitchen-viewer).
  // El alias existe para que el jefe de cocina pueda pegar /admin/kds en el browser sin
  // tener que recordar el path largo. No aparece en sidebar (el icono está en el topbar).
  {
    path: 'kds',
    redirect: { name: 'admin.pos.kitchen_viewer' },
  },

  // Cocina hub — impresoras + KDS + reglas (placeholder).
  {
    path: 'cocina',
    component: () => import('@/modules/admin/pages/admin/hubs/CocinaHub.vue'),
    meta: { title: 'admin::sidebar.cocina' },
    children: [
      { path: '', redirect: { name: 'admin.cocina.impresoras' } },
      {
        path: 'impresoras',
        name: 'admin.cocina.impresoras',
        component: () => import('@/modules/printer/pages/admin/printer/Index.vue'),
        meta: { permission: 'admin.printers.index' },
      },
      {
        // KDS full-screen sigue siendo ruta aparte (admin.pos.kitchen_viewer)
        // con icono en el top bar. Esta tab ahora hosta la configuracion
        // del KDS (checkbox auto-refresh + pause_on_idle) — antes estaba
        // como tab separada "ajustes". Fusion hace un solo tab para todo
        // lo del display de cocina: settings + deeplink al full-screen.
        path: 'kds',
        name: 'admin.cocina.kds',
        component: () => import('@/modules/setting/pages/admin/setting/Kitchen.vue'),
        meta: { permission: 'admin.settings.edit' },
      },
      {
        path: 'reglas-de-impresion',
        name: 'admin.cocina.reglas',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
    ],
  },

  // Cobros hub ELIMINADO: Formas de cobro vive ahora en Configuración > Operación.
  // Los paths viejos redirigen para no romper bookmarks. Cualquier /admin/cobros/*
  // (formas-de-pago, impuestos, motivos, caja) cae en la nueva ubicación.
  {
    path: 'cobros/:pathMatch(.*)*',
    name: 'admin.cobros.legacy_redirect',
    redirect: { name: 'admin.configuracion.operacion.formas' },
  },

  // Personal hub — usuarios, jobs (placeholder), permisos, turnos (placeholder).
  // Arqueos se movio a la pantalla Caja del POS. El Index de pos/session
  // queda como componente orfano, reutilizable si luego hacemos un
  // reporte historico de sesiones.
  {
    path: 'personal',
    component: () => import('@/modules/admin/pages/admin/hubs/PersonalHub.vue'),
    meta: { title: 'admin::sidebar.personal' },
    children: [
      { path: '', redirect: { name: 'admin.personal.usuarios' } },
      {
        path: 'usuarios',
        name: 'admin.personal.usuarios',
        component: () => import('@/modules/user/pages/admin/user/Index.vue'),
        meta: { permission: 'admin.users.index' },
      },
      {
        path: 'jobs',
        name: 'admin.personal.jobs',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
      {
        path: 'permisos',
        name: 'admin.personal.permisos',
        component: () => import('@/modules/user/pages/admin/role/Index.vue'),
        meta: { permission: 'admin.roles.index' },
      },
      {
        path: 'turnos',
        name: 'admin.personal.turnos',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
    ],
  },

  // Marketing hub — descuentos, cupones, gift cards, fidelizacion, clientes.
  {
    path: 'marketing',
    component: () => import('@/modules/admin/pages/admin/hubs/MarketingHub.vue'),
    meta: { title: 'admin::sidebar.marketing' },
    children: [
      { path: '', redirect: { name: 'admin.marketing.descuentos' } },
      {
        // Bloque 4.4a: descuentos absorbe tambien las loyalty promotions.
        // Renderiza DiscountIndex + LoyaltyPromotionIndex stackeados.
        path: 'descuentos',
        name: 'admin.marketing.descuentos',
        component: () => import('@/modules/admin/pages/admin/hubs/partials/DescuentosConsolidated.vue'),
        meta: { permission: 'admin.discounts.index' },
      },
      {
        path: 'cupones',
        name: 'admin.marketing.cupones',
        component: () => import('@/modules/promotion/pages/admin/voucher/Index.vue'),
        meta: { permission: 'admin.vouchers.index' },
      },
      {
        path: 'tarjetas-de-regalo',
        name: 'admin.marketing.gift_cards',
        component: () => import('@/modules/giftcard/pages/admin/giftCard/Index.vue'),
        meta: { permission: 'admin.gift_cards.index' },
      },
      {
        path: 'fidelizacion',
        name: 'admin.marketing.fidelizacion',
        component: () => import('@/modules/loyalty/pages/admin/program/Index.vue'),
        meta: { permission: 'admin.loyalty_programs.index' },
      },
      {
        path: 'clientes',
        name: 'admin.marketing.clientes',
        component: () => import('@/modules/user/pages/admin/customer/Index.vue'),
        meta: { permission: 'admin.customers.index' },
      },
    ],
  },

  // Reportes — sin tabs.
  {
    path: 'reportes',
    name: 'admin.reportes',
    component: () => import('@/modules/report/pages/admin/report/Index.vue'),
    meta: {
      title: 'admin::sidebar.reports',
      permission: 'admin.reports.index',
    },
  },

  // Anti-fraude dashboard + pending approvals admin.
  {
    path: 'antifraud',
    name: 'admin.antifraud',
    component: () => import('@/modules/antifraud/pages/admin/Index.vue'),
    meta: {
      title: 'admin::sidebar.antifraud',
      permission: 'admin.audit_logs.index',
    },
  },
  {
    path: 'pending-approvals',
    name: 'admin.pending_approvals',
    component: () => import('@/modules/antifraud/pages/admin/PendingApprovals.vue'),
    meta: {
      title: 'admin::sidebar.pending_approvals',
      permission: 'admin.audit_logs.index',
    },
  },

  // Configuración reorganizada: landing con 4 cards (Restaurante / Operación /
  // Usuarios y seguridad / Sistema) y sub-hubs con tabs por grupo.
  // Rutas flat porque el parent no puede ser a la vez landing + pass-through.
  // Cualquier /admin/configuracion/* viejo queda en redirects catch-all abajo.
  {
    path: 'configuracion',
    name: 'admin.configuracion',
    component: () => import('@/modules/admin/pages/admin/hubs/ConfiguracionLanding.vue'),
    meta: { title: 'admin::sidebar.configuracion' },
  },
  {
    path: 'configuracion/restaurante',
    name: 'admin.configuracion.restaurante',
    component: () => import('@/modules/admin/pages/admin/hubs/configuracion/RestauranteSubHub.vue'),
    meta: {
      title: 'admin::admin.configuracion_landing.cards.restaurante.title',
      permission: 'admin.settings.edit',
    },
  },
  {
    path: 'configuracion/operacion',
    component: () => import('@/modules/admin/pages/admin/hubs/configuracion/OperacionSubHub.vue'),
    meta: { title: 'admin::admin.configuracion_landing.cards.operacion.title' },
    redirect: { name: 'admin.configuracion.operacion.formas' },
    children: [
      {
        path: 'formas-de-cobro',
        name: 'admin.configuracion.operacion.formas',
        component: () => import('@/modules/payment/pages/admin/paymentMethod/Index.vue'),
        meta: { permission: 'admin.payment_methods.index' },
      },
      {
        path: 'impresion',
        name: 'admin.configuracion.operacion.impresion',
        component: () => import('@/modules/admin/pages/admin/hubs/partials/ImpresionConsolidated.vue'),
        meta: { permission: 'admin.printers.index' },
      },
      {
        path: 'kds',
        name: 'admin.configuracion.operacion.kds',
        component: () => import('@/modules/setting/pages/admin/setting/Kitchen.vue'),
        meta: { permission: 'admin.settings.edit' },
      },
    ],
  },
  {
    path: 'configuracion/usuarios-y-seguridad',
    component: () => import('@/modules/admin/pages/admin/hubs/configuracion/UsuariosSeguridadSubHub.vue'),
    meta: { title: 'admin::admin.configuracion_landing.cards.users_and_security.title' },
    redirect: { name: 'admin.configuracion.usuarios_seguridad.usuarios_permisos' },
    children: [
      {
        path: 'usuarios-y-permisos',
        name: 'admin.configuracion.usuarios_seguridad.usuarios_permisos',
        component: () => import('@/modules/admin/pages/admin/hubs/partials/UsuariosPermisosConsolidated.vue'),
        meta: { permission: 'admin.users.index' },
      },
      {
        path: 'antifraud',
        name: 'admin.configuracion.usuarios_seguridad.antifraud',
        component: () => import('@/modules/setting/pages/admin/setting/Antifraud.vue'),
        meta: { permission: 'admin.settings.edit' },
      },
    ],
  },
  {
    path: 'configuracion/sistema',
    component: () => import('@/modules/admin/pages/admin/hubs/configuracion/SistemaSubHub.vue'),
    meta: { title: 'admin::admin.configuracion_landing.cards.system.title' },
    redirect: { name: 'admin.configuracion.sistema.correo' },
    children: [
      {
        path: 'correo',
        name: 'admin.configuracion.sistema.correo',
        component: () => import('@/modules/setting/pages/admin/setting/Mail.vue'),
        meta: { permission: 'admin.settings.edit' },
      },
      {
        path: 'afip',
        name: 'admin.configuracion.sistema.afip',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
      {
        path: 'integraciones',
        name: 'admin.configuracion.sistema.integraciones',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
      {
        path: 'herramientas',
        name: 'admin.configuracion.sistema.herramientas',
        component: () => import('@/modules/tool/pages/admin/database/Index.vue'),
        meta: { permission: 'admin.tools.database' },
      },
      {
        path: 'soporte-tecnico',
        name: 'admin.configuracion.sistema.soporte',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
    ],
  },

  // Redirects desde rutas legacy (bloque 6) — si alguien bookmarked alguna tab
  // vieja de Configuración, la mandamos al nuevo hogar más cercano.
  { path: 'configuracion/general', redirect: { name: 'admin.configuracion.restaurante' } },
  { path: 'configuracion/usuarios-y-permisos', redirect: { name: 'admin.configuracion.usuarios_seguridad.usuarios_permisos' } },
  { path: 'configuracion/impresion', redirect: { name: 'admin.configuracion.operacion.impresion' } },
  { path: 'configuracion/correo', redirect: { name: 'admin.configuracion.sistema.correo' } },
  { path: 'configuracion/afip', redirect: { name: 'admin.configuracion.sistema.afip' } },
  { path: 'configuracion/antifraud', redirect: { name: 'admin.configuracion.usuarios_seguridad.antifraud' } },
  { path: 'configuracion/integraciones', redirect: { name: 'admin.configuracion.sistema.integraciones' } },
  { path: 'configuracion/herramientas', redirect: { name: 'admin.configuracion.sistema.herramientas' } },
  { path: 'configuracion/soporte-tecnico', redirect: { name: 'admin.configuracion.sistema.soporte' } },
]

export default adminRoutes
