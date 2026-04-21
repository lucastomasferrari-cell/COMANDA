import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'branches',
    name: 'admin.branches',
    meta: {
      title: 'admin::sidebar.branches',
      icon: 'tabler-git-branch',
    },
    children: [
      {
        path: '',
        name: 'admin.branches.index',
        component: () => import('@/modules/branch/pages/admin/branch/Index.vue'),
        meta: {
          permission: 'admin.branches.index',
        },
      },
      {
        path: 'create',
        name: 'admin.branches.create',
        component: () => import('@/modules/branch/pages/admin/branch/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'branch::branches.branch' },
          permission: 'admin.branches.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.branches.edit',
        component: () => import('@/modules/branch/pages/admin/branch/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'branch::branches.branch' },
          permission: 'admin.branches.edit',
        },
      },
    ],
  },
]

export default adminRoutes
