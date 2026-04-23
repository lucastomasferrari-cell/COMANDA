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
      {
        path: 'notificaciones',
        name: 'admin.salon.notificaciones',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
    ],
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

  // Cobros hub — formas (placeholder), impuestos, motivos, caja.
  {
    path: 'cobros',
    component: () => import('@/modules/admin/pages/admin/hubs/CobrosHub.vue'),
    meta: { title: 'admin::sidebar.cobros' },
    children: [
      { path: '', redirect: { name: 'admin.cobros.formas' } },
      {
        path: 'formas-de-pago',
        name: 'admin.cobros.formas',
        component: () => import('@/modules/payment/pages/admin/paymentMethod/Index.vue'),
        meta: { permission: 'admin.payment_methods.index' },
      },
      {
        path: 'impuestos',
        name: 'admin.cobros.impuestos',
        component: () => import('@/modules/tax/pages/admin/tax/Index.vue'),
        meta: { permission: 'admin.taxes.index' },
      },
      {
        path: 'motivos',
        name: 'admin.cobros.motivos',
        component: () => import('@/modules/sale/pages/admin/reason/Index.vue'),
        meta: { permission: 'admin.reasons.index' },
      },
      {
        path: 'caja',
        name: 'admin.cobros.caja',
        component: () => import('@/modules/pos/pages/admin/register/Index.vue'),
        meta: { permission: 'admin.pos_registers.index' },
      },
    ],
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

  // Configuracion hub — general, apariencia (placeholder), AFIP (placeholder), integraciones (placeholder), herramientas.
  {
    path: 'configuracion',
    component: () => import('@/modules/admin/pages/admin/hubs/ConfiguracionHub.vue'),
    meta: { title: 'admin::sidebar.configuracion' },
    children: [
      { path: '', redirect: { name: 'admin.configuracion.general' } },
      {
        path: 'general',
        name: 'admin.configuracion.general',
        component: () => import('@/modules/setting/pages/admin/setting/General.vue'),
        meta: { permission: 'admin.settings.edit' },
      },
      {
        path: 'apariencia',
        name: 'admin.configuracion.apariencia',
        component: () => import('@/modules/setting/pages/admin/setting/Appearance.vue'),
        meta: { permission: 'admin.settings.edit' },
      },
      {
        path: 'afip',
        name: 'admin.configuracion.afip',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
      {
        path: 'integraciones',
        name: 'admin.configuracion.integraciones',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
      {
        path: 'antifraud',
        name: 'admin.configuracion.antifraud',
        component: () => import('@/modules/setting/pages/admin/setting/Antifraud.vue'),
        meta: { permission: 'admin.settings.edit' },
      },
      {
        path: 'herramientas',
        name: 'admin.configuracion.herramientas',
        component: () => import('@/modules/tool/pages/admin/database/Index.vue'),
        meta: { permission: 'admin.tools.database' },
      },
    ],
  },
]

export default adminRoutes
