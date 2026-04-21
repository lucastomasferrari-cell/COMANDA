import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'printers',
    name: 'admin.printers',
    meta: {
      title: 'admin::sidebar.printers',
      icon: 'tabler-printer',
    },
    children: [
      {
        path: '',
        name: 'admin.printers.index',
        component: () => import('@/modules/printer/pages/admin/printer/Index.vue'),
        meta: {
          permission: 'admin.printers.index',
        },
      },
      {
        path: 'create',
        name: 'admin.printers.create',
        component: () => import('@/modules/printer/pages/admin/printer/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'printer::printers.printer' },
          permission: 'admin.printers.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.printers.edit',
        component: () => import('@/modules/printer/pages/admin/printer/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'printer::printers.printer' },
          permission: 'admin.printers.edit',
        },
      },
    ],
  },
  {
    path: 'print-agents',
    name: 'admin.print_agents',
    meta: {
      title: 'admin::sidebar.print_agents',
      icon: 'tabler-tournament',
    },
    children: [
      {
        path: '',
        name: 'admin.print_agents.index',
        component: () => import('@/modules/printer/pages/admin/printAgent/Index.vue'),
        meta: {
          permission: 'admin.print_agents.index',
        },
      },
      {
        path: 'create',
        name: 'admin.print_agents.create',
        component: () => import('@/modules/printer/pages/admin/printAgent/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'printer::print_agents.print_agent' },
          permission: 'admin.print_agents.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.print_agents.edit',
        component: () => import('@/modules/printer/pages/admin/printAgent/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'printer::print_agents.print_agent' },
          permission: 'admin.print_agents.edit',
        },
      },
    ],
  },
]

export default adminRoutes
