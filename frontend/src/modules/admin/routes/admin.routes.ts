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

  // Menu hub — 4 tabs (Productos, Categorias, Opciones, Menus).
  {
    path: 'menu',
    component: () => import('@/modules/admin/pages/admin/hubs/MenuHub.vue'),
    meta: { title: 'admin::sidebar.menu' },
    children: [
      { path: '', redirect: { name: 'admin.menu.productos' } },
      {
        path: 'productos',
        name: 'admin.menu.productos',
        component: () => import('@/modules/menu/pages/admin/product/Index.vue'),
        meta: { permission: 'admin.products.index' },
      },
      {
        path: 'categorias',
        name: 'admin.menu.categorias',
        component: () => import('@/modules/menu/pages/admin/category/Index.vue'),
        meta: { permission: 'admin.categories.index' },
      },
      {
        path: 'opciones',
        name: 'admin.menu.opciones',
        component: () => import('@/modules/menu/pages/admin/option/Index.vue'),
        meta: { permission: 'admin.options.index' },
      },
      {
        path: 'menus',
        name: 'admin.menu.menus',
        component: () => import('@/modules/menu/pages/admin/menu/Index.vue'),
        meta: { permission: 'admin.menus.index' },
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
        // KDS existe como ruta full-screen aparte (admin.pos.kitchen_viewer).
        // Esta tab renderiza un placeholder con enlace por si el operador
        // cayo aca desde el sidebar. El sidebar top-level linkea directo.
        path: 'kds',
        name: 'admin.cocina.kds',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
      {
        path: 'reglas-de-impresion',
        name: 'admin.cocina.reglas',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
    ],
  },

  // Pagos hub — formas (placeholder), impuestos, motivos, caja.
  {
    path: 'pagos',
    component: () => import('@/modules/admin/pages/admin/hubs/PagosHub.vue'),
    meta: { title: 'admin::sidebar.pagos' },
    children: [
      { path: '', redirect: { name: 'admin.pagos.formas' } },
      {
        path: 'formas-de-pago',
        name: 'admin.pagos.formas',
        component: () => import('@/modules/core/components/ComingSoonPlaceholder.vue'),
      },
      {
        path: 'impuestos',
        name: 'admin.pagos.impuestos',
        component: () => import('@/modules/tax/pages/admin/tax/Index.vue'),
        meta: { permission: 'admin.taxes.index' },
      },
      {
        path: 'motivos',
        name: 'admin.pagos.motivos',
        component: () => import('@/modules/sale/pages/admin/reason/Index.vue'),
        meta: { permission: 'admin.reasons.index' },
      },
      {
        path: 'caja',
        name: 'admin.pagos.caja',
        component: () => import('@/modules/pos/pages/admin/register/Index.vue'),
        meta: { permission: 'admin.pos_registers.index' },
      },
    ],
  },

  // Personal hub — usuarios, jobs (placeholder), permisos, turnos (placeholder), arqueos.
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
      {
        path: 'arqueos',
        name: 'admin.personal.arqueos',
        component: () => import('@/modules/pos/pages/admin/session/Index.vue'),
        meta: { permission: 'admin.pos_sessions.index' },
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
        path: 'descuentos',
        name: 'admin.marketing.descuentos',
        component: () => import('@/modules/promotion/pages/admin/discount/Index.vue'),
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
        path: 'herramientas',
        name: 'admin.configuracion.herramientas',
        component: () => import('@/modules/tool/pages/admin/database/Index.vue'),
        meta: { permission: 'admin.tools.database' },
      },
    ],
  },
]

export default adminRoutes
