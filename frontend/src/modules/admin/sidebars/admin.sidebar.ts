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
    // Salón: removido (sprint 4 cards). Plano de mesas/Zonas → Configuración > Plano de mesas.
    //   Canales de venta → Configuración > Operación.
    //   /admin/salon/* redirecciona a su nueva ubicación en bloque 3.
    // Cocina: removido (bloque 5). Impresoras → Configuración > Operación > Impresión.
    //   KDS viewer → /admin/kds (alias) o topbar icon.
    // Personal: removido (bloque 5). Usuarios/Permisos → Configuración > Usuarios y seguridad.
    // Cobros: removido (fix 2). Formas de cobro → Configuración > Operación > Formas de cobro.
    //   /admin/cobros/* sigue funcionando vía redirect catch-all.
    // Marketing: removido (sprint 4 cards). Clientes sube como top-level.
    //   Descuentos/Cupones/Gift cards/Fidelización se reactivan en v2.
    //   /admin/marketing/* redirect catch-all → /admin/clientes.
    {
      key: 'admin.clientes',
      label: 'admin::sidebar.customers',
      to: { name: 'admin.clientes' },
      icon: 'tabler-user-circle',
      permission: 'admin.customers.index',
      sort: 3,
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
      icon: 'tabler-shield-check',
      permission: 'admin.audit_logs.index',
      sort: 9,
    },
    {
      key: 'admin.configuracion',
      label: 'admin::sidebar.configuracion',
      to: { name: 'admin.configuracion' },
      icon: 'tabler-settings',
      permission: [
        'admin.settings.edit',
        'admin.tools.database',
      ],
      sort: 10,
    },
  ],
}
