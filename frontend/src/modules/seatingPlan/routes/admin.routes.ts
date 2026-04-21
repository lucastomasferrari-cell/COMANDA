import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'tables',
    name: 'admin.tables',
    meta: {
      title: 'admin::sidebar.tables',
      icon: 'tabler-brand-airtable',
    },
    children: [
      {
        path: '',
        name: 'admin.tables.index',
        component: () => import('@/modules/seatingPlan/pages/admin/table/Index.vue'),
        meta: {
          permission: 'admin.tables.index',
        },
      },
      {
        path: 'create',
        name: 'admin.tables.create',
        component: () => import('@/modules/seatingPlan/pages/admin/table/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'seatingplan::tables.table' },
          permission: 'admin.tables.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.tables.edit',
        component: () => import('@/modules/seatingPlan/pages/admin/table/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'seatingplan::tables.table' },
          permission: 'admin.tables.edit',
        },
      },
      {
        path: ':id/show',
        name: 'admin.tables.show',
        component: () => import('@/modules/seatingPlan/pages/admin/table/Show.vue'),
        meta: {
          title: 'admin::resource.show',
          transParam: { resource: 'seatingplan::tables.table' },
          permission: 'admin.tables.show',
        },
      },
    ],
  },
  {
    path: 'table-merges',
    name: 'admin.table_merges',
    meta: {
      title: 'admin::sidebar.table_merges',
      icon: 'tabler-arrow-merge',
    },
    children: [
      {
        path: '',
        name: 'admin.table_merges.index',
        component: () => import('@/modules/seatingPlan/pages/admin/tableMerge/Index.vue'),
        meta: {
          permission: 'admin.table_merges.index',
        },
      },
    ],
  },
  {
    path: 'zones',
    name: 'admin.zones',
    meta: {
      title: 'admin::sidebar.zones',
      icon: 'tabler-layout-kanban',
    },
    children: [
      {
        path: '',
        name: 'admin.zones.index',
        component: () => import('@/modules/seatingPlan/pages/admin/zone/Index.vue'),
        meta: {
          permission: 'admin.zones.index',
        },
      },
      {
        path: 'create',
        name: 'admin.zones.create',
        component: () => import('@/modules/seatingPlan/pages/admin/zone/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'seatingplan::zones.zone' },
          permission: 'admin.zones.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.zones.edit',
        component: () => import('@/modules/seatingPlan/pages/admin/zone/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'seatingplan::zones.zone' },
          permission: 'admin.zones.edit',
        },
      },
    ],
  },
  {
    path: 'floors',
    name: 'admin.floors',
    meta: {
      title: 'admin::sidebar.floors',
      icon: 'tabler-layers-difference',
    },
    children: [
      {
        path: '',
        name: 'admin.floors.index',
        component: () => import('@/modules/seatingPlan/pages/admin/floor/Index.vue'),
        meta: {
          permission: 'admin.floors.index',
        },
      },
      {
        path: 'create',
        name: 'admin.floors.create',
        component: () => import('@/modules/seatingPlan/pages/admin/floor/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'seatingplan::floors.floor' },
          permission: 'admin.floors.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.floors.edit',
        component: () => import('@/modules/seatingPlan/pages/admin/floor/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'seatingplan::floors.floor' },
          permission: 'admin.floors.edit',
        },
      },
    ],
  },
]

export default adminRoutes
