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

  // Salón ELIMINADO (sprint 5 cards): Plano de mesas → Configuración > Plano de mesas.
  //   Canales de venta → Configuración > Operación.
  //   Catch-all redirect de /admin/salon/* abajo. Rutas específicas redirigen a
  //   su nuevo hogar; el resto cae en la landing de Configuración.
  {
    path: 'salon/plano-de-mesas',
    redirect: { name: 'admin.configuracion.plano_de_mesas.visual' },
  },
  {
    path: 'salon/zonas',
    redirect: { name: 'admin.configuracion.plano_de_mesas.zonas' },
  },
  {
    path: 'salon/canales-de-venta',
    redirect: { name: 'admin.configuracion.operacion.canales' },
  },
  {
    path: 'salon/:pathMatch(.*)*',
    name: 'admin.salon.legacy_redirect',
    redirect: { name: 'admin.configuracion' },
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

  // Marketing ELIMINADO del sidebar (descuentos/cupones/gift-cards/loyalty
  // se reactivan en v2). Clientes (que era hijo de marketing) sube a
  // top-level como /admin/clientes. Catch-all redirect manda cualquier
  // bookmark viejo de /admin/marketing/* a /admin/clientes.
  {
    path: 'marketing/:pathMatch(.*)*',
    name: 'admin.marketing.legacy_redirect',
    redirect: { name: 'admin.clientes' },
  },

  // Clientes top-level con create/edit anidados. Mismo patrón del Bloque 1:
  // el Create/Edit renderea dentro del router-view de este bloque sin salir
  // del hub. No necesita sub-tabs (single resource) — el header del form
  // viene de BaseForm.
  {
    path: 'clientes',
    meta: {
      title: 'admin::sidebar.customers',
      icon: 'tabler-user-circle',
    },
    children: [
      {
        path: '',
        name: 'admin.clientes',
        component: () => import('@/modules/user/pages/admin/customer/Index.vue'),
        meta: { permission: 'admin.customers.index' },
      },
      {
        path: 'create',
        name: 'admin.clientes.create',
        component: () => import('@/modules/user/pages/admin/customer/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'user::customers.customer' },
          permission: 'admin.customers.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.clientes.edit',
        component: () => import('@/modules/user/pages/admin/customer/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'user::customers.customer' },
          permission: 'admin.customers.edit',
        },
      },
    ],
  },
  // Redirect de /admin/customers/* (ruta vendor-style del módulo user) hacia
  // /admin/clientes/*. Mantenemos la ruta vendor viva en modules/user/routes/
  // pero el user admin siempre llega a /admin/clientes.
  {
    path: 'customers/:pathMatch(.*)*',
    name: 'admin.customers.legacy_redirect',
    redirect: { name: 'admin.clientes' },
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
    name: 'admin.configuracion.operacion',
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
      // Create/Edit anidados bajo formas-de-cobro: cuando estás en estas rutas,
      // OperacionSubHub sigue montado con PageTabs visibles (la tab "Formas"
      // queda active porque el path starts con formas-de-cobro). Al submit/cancel
      // el form vuelve al Index siblings-mismo-sub-hub sin perder contexto.
      {
        path: 'formas-de-cobro/create',
        name: 'admin.configuracion.operacion.formas.create',
        component: () => import('@/modules/payment/pages/admin/paymentMethod/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'payment::payment_methods.payment_method' },
          permission: 'admin.payment_methods.create',
        },
      },
      {
        path: 'formas-de-cobro/:id/edit',
        name: 'admin.configuracion.operacion.formas.edit',
        component: () => import('@/modules/payment/pages/admin/paymentMethod/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'payment::payment_methods.payment_method' },
          permission: 'admin.payment_methods.edit',
        },
      },
      {
        path: 'impresion',
        name: 'admin.configuracion.operacion.impresion',
        component: () => import('@/modules/admin/pages/admin/hubs/partials/ImpresionConsolidated.vue'),
        meta: { permission: 'admin.printers.index' },
      },
      {
        path: 'impresion/create',
        name: 'admin.configuracion.operacion.impresion.create',
        component: () => import('@/modules/printer/pages/admin/printer/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'printer::printers.printer' },
          permission: 'admin.printers.create',
        },
      },
      {
        path: 'impresion/:id/edit',
        name: 'admin.configuracion.operacion.impresion.edit',
        component: () => import('@/modules/printer/pages/admin/printer/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'printer::printers.printer' },
          permission: 'admin.printers.edit',
        },
      },
      {
        path: 'kds',
        name: 'admin.configuracion.operacion.kds',
        component: () => import('@/modules/setting/pages/admin/setting/Kitchen.vue'),
        meta: { permission: 'admin.settings.edit' },
      },
      // Canales de venta migrado desde el hub Salón eliminado. Placeholder
      // por ahora — la feature (online ordering, delivery apps, QR table)
      // se activa cuando estén las integraciones.
      {
        path: 'canales-de-venta',
        name: 'admin.configuracion.operacion.canales',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
    ],
  },
  // Configuración > Plano de mesas — sub-hub nuevo con 2 tabs (Plano visual + Zonas).
  // Migrado desde el hub Salón eliminado en el sprint 5 cards.
  {
    path: 'configuracion/plano-de-mesas',
    name: 'admin.configuracion.plano_de_mesas',
    component: () => import('@/modules/admin/pages/admin/hubs/configuracion/PlanoDeMesasSubHub.vue'),
    meta: { title: 'admin::admin.configuracion_landing.cards.plano_de_mesas.title' },
    redirect: { name: 'admin.configuracion.plano_de_mesas.visual' },
    children: [
      {
        path: 'plano-visual',
        name: 'admin.configuracion.plano_de_mesas.visual',
        component: () => import('@/modules/seatingPlan/pages/admin/SalonPlanoView.vue'),
        meta: { permission: 'admin.tables.index' },
      },
      {
        path: 'zonas',
        name: 'admin.configuracion.plano_de_mesas.zonas',
        component: () => import('@/modules/seatingPlan/pages/admin/zone/Index.vue'),
        meta: { permission: 'admin.zones.index' },
      },
    ],
  },
  {
    path: 'configuracion/usuarios-y-seguridad',
    name: 'admin.configuracion.usuarios_seguridad',
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
      // Create/Edit anidados separados por entidad (users / roles) porque
      // UsuariosPermisosConsolidated stackea ambos Index y cada uno tiene su
      // own CRUD. Sufijo en el path lo discrimina.
      {
        path: 'usuarios-y-permisos/users/create',
        name: 'admin.configuracion.usuarios_seguridad.users.create',
        component: () => import('@/modules/user/pages/admin/user/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'user::users.user' },
          permission: 'admin.users.create',
        },
      },
      {
        path: 'usuarios-y-permisos/users/:id/edit',
        name: 'admin.configuracion.usuarios_seguridad.users.edit',
        component: () => import('@/modules/user/pages/admin/user/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'user::users.user' },
          permission: 'admin.users.edit',
        },
      },
      {
        path: 'usuarios-y-permisos/roles/create',
        name: 'admin.configuracion.usuarios_seguridad.roles.create',
        component: () => import('@/modules/user/pages/admin/role/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'user::roles.role' },
          permission: 'admin.roles.create',
        },
      },
      {
        path: 'usuarios-y-permisos/roles/:id/edit',
        name: 'admin.configuracion.usuarios_seguridad.roles.edit',
        component: () => import('@/modules/user/pages/admin/role/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'user::roles.role' },
          permission: 'admin.roles.edit',
        },
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
    name: 'admin.configuracion.sistema',
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
