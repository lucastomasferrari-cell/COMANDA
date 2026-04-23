import type { SidebarList } from '@/modules/core/contracts/SidebarItem.ts'

/**
 * Sidebar principal del admin: 9 entradas top-level.
 *
 * Cada entrada con tabs abre una pantalla hub (/admin/{hub}) que monta
 * PageTabs y redirige a la primera tab. Las rutas viejas (/admin/products,
 * /admin/tables, etc.) siguen funcionando - no se borraron.
 *
 * Los sidebar items de cada modulo (menu/sidebars/admin.sidebar.ts,
 * pos/sidebars/..., branch/sidebars/..., inventory/..., activity/...,
 * currency/..., translation/..., etc.) estan marcados con disable:true
 * en esos archivos para no aparecer en el menu. El codigo queda intacto
 * para facilitar futuros updates del vendor.
 */
export const adminSidebar: SidebarList = {
  target: 'admin',
  items: [
    {
      key: 'admin.dashboard',
      label: 'admin::sidebar.dashboard',
      to: { name: 'admin.dashboard' },
      icon: 'tabler-layout-dashboard',
      sort: 1,
    },
    {
      key: 'admin.menu',
      label: 'admin::sidebar.menu',
      to: { name: 'admin.menu.productos' },
      icon: 'tabler-list-details',
      permission: [
        'admin.products.index',
        'admin.categories.index',
        'admin.options.index',
        'admin.menus.index',
      ],
      sort: 2,
    },
    {
      key: 'admin.salon',
      label: 'admin::sidebar.salon',
      to: { name: 'admin.salon.plano' },
      icon: 'tabler-building-skyscraper',
      permission: 'admin.tables.index',
      sort: 3,
    },
    {
      key: 'admin.cocina',
      label: 'admin::sidebar.cocina',
      to: { name: 'admin.cocina.impresoras' },
      icon: 'tabler-chef-hat',
      permission: [
        'admin.printers.index',
        'admin.pos.kitchen_viewer',
      ],
      sort: 4,
    },
    {
      key: 'admin.cobros',
      label: 'admin::sidebar.cobros',
      to: { name: 'admin.cobros.impuestos' },
      icon: 'tabler-cash',
      permission: [
        'admin.taxes.index',
        'admin.reasons.index',
        'admin.pos_registers.index',
      ],
      sort: 5,
    },
    {
      key: 'admin.personal',
      label: 'admin::sidebar.personal',
      to: { name: 'admin.personal.usuarios' },
      icon: 'tabler-users-group',
      permission: [
        'admin.users.index',
        'admin.roles.index',
        'admin.pos_sessions.index',
      ],
      sort: 6,
    },
    {
      key: 'admin.marketing',
      label: 'admin::sidebar.marketing',
      to: { name: 'admin.marketing.descuentos' },
      icon: 'tabler-target-arrow',
      permission: [
        'admin.discounts.index',
        'admin.vouchers.index',
        'admin.gift_cards.index',
        'admin.loyalty_programs.index',
        'admin.customers.index',
      ],
      sort: 7,
    },
    {
      key: 'admin.reportes',
      label: 'admin::sidebar.reports',
      to: { name: 'admin.reportes' },
      icon: 'tabler-chart-histogram',
      permission: 'admin.reports.index',
      sort: 8,
    },
    {
      key: 'admin.antifraud',
      label: 'admin::sidebar.antifraud',
      to: { name: 'admin.antifraud' },
      icon: 'tabler-shield-lock',
      permission: 'admin.audit_logs.index',
      sort: 9,
    },
    {
      key: 'admin.configuracion',
      label: 'admin::sidebar.configuracion',
      to: { name: 'admin.configuracion.general' },
      icon: 'tabler-settings',
      permission: [
        'admin.settings.edit',
        'admin.tools.database',
      ],
      sort: 10,
    },
  ],
}
