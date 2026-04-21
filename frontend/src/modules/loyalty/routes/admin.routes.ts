import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'loyalty-customers',
    name: 'admin.loyalty_customers',
    meta: {
      title: 'loyalty::loyalty_customers.loyalty_customers',
      icon: 'tabler-user-bolt',
    },
    children: [
      {
        path: '',
        name: 'admin.loyalty_customers.index',
        component: () => import('@/modules/loyalty/pages/admin/customer/Index.vue'),
        meta: {
          permission: 'admin.loyalty_customers.index',
        },
      },
    ],
  },
  {
    path: 'loyalty-transactions',
    name: 'admin.loyalty_transactions',
    meta: {
      title: 'loyalty::loyalty_transactions.loyalty_transactions',
      icon: 'tabler-arrows-exchange',
    },
    children: [
      {
        path: '',
        name: 'admin.loyalty_transactions.index',
        component: () => import('@/modules/loyalty/pages/admin/transaction/Index.vue'),
        meta: {
          permission: 'admin.loyalty_transactions.index',
        },
      },
    ],
  },
  {
    path: 'loyalty-programs',
    name: 'admin.loyalty_programs',
    meta: {
      title: 'loyalty::loyalty_programs.loyalty_programs',
      icon: 'tabler-medal',
    },
    children: [
      {
        path: '',
        name: 'admin.loyalty_programs.index',
        component: () => import('@/modules/loyalty/pages/admin/program/Index.vue'),
        meta: {
          permission: 'admin.loyalty_programs.index',
        },
      },
      {
        path: 'create',
        name: 'admin.loyalty_programs.create',
        component: () => import('@/modules/loyalty/pages/admin/program/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'loyalty::loyalty_programs.loyalty_program' },
          permission: 'admin.loyalty_programs.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.loyalty_programs.edit',
        component: () => import('@/modules/loyalty/pages/admin/program/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'loyalty::loyalty_programs.loyalty_program' },
          permission: 'admin.loyalty_programs.edit',
        },
      },
    ],
  },
  {
    path: 'loyalty-tiers',
    name: 'admin.loyalty_tiers',
    meta: {
      title: 'loyalty::loyalty_tiers.loyalty_tiers',
      icon: 'tabler-diamond',
    },
    children: [
      {
        path: '',
        name: 'admin.loyalty_tiers.index',
        component: () => import('@/modules/loyalty/pages/admin/tier/Index.vue'),
        meta: {
          permission: 'admin.loyalty_tiers.index',
        },
      },
      {
        path: 'create',
        name: 'admin.loyalty_tiers.create',
        component: () => import('@/modules/loyalty/pages/admin/tier/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'loyalty::loyalty_tiers.loyalty_tier' },
          permission: 'admin.loyalty_tiers.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.loyalty_tiers.edit',
        component: () => import('@/modules/loyalty/pages/admin/tier/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'loyalty::loyalty_tiers.loyalty_tier' },
          permission: 'admin.loyalty_tiers.edit',
        },
      },
    ],
  },
  {
    path: 'loyalty-rewards',
    name: 'admin.loyalty_rewards',
    meta: {
      title: 'loyalty::loyalty_rewards.loyalty_rewards',
      icon: 'tabler-trophy',
    },
    children: [
      {
        path: '',
        name: 'admin.loyalty_rewards.index',
        component: () => import('@/modules/loyalty/pages/admin/reward/Index.vue'),
        meta: {
          permission: 'admin.loyalty_rewards.index',
        },
      },
      {
        path: 'create',
        name: 'admin.loyalty_rewards.create',
        component: () => import('@/modules/loyalty/pages/admin/reward/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'loyalty::loyalty_rewards.loyalty_reward' },
          permission: 'admin.loyalty_rewards.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.loyalty_rewards.edit',
        component: () => import('@/modules/loyalty/pages/admin/reward/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'loyalty::loyalty_rewards.loyalty_reward' },
          permission: 'admin.loyalty_rewards.edit',
        },
      },
    ],
  },
  {
    path: 'loyalty-promotions',
    name: 'admin.loyalty_promotions',
    meta: {
      title: 'loyalty::loyalty_promotions.loyalty_promotions',
      icon: 'tabler-sparkles',
    },
    children: [
      {
        path: '',
        name: 'admin.loyalty_promotions.index',
        component: () => import('@/modules/loyalty/pages/admin/promotion/Index.vue'),
        meta: {
          permission: 'admin.loyalty_promotions.index',
        },
      },
      {
        path: 'create',
        name: 'admin.loyalty_promotions.create',
        component: () => import('@/modules/loyalty/pages/admin/promotion/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'loyalty::loyalty_promotions.loyalty_promotion' },
          permission: 'admin.loyalty_promotions.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.loyalty_promotions.edit',
        component: () => import('@/modules/loyalty/pages/admin/promotion/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'loyalty::loyalty_promotions.loyalty_promotion' },
          permission: 'admin.loyalty_promotions.edit',
        },
      },
    ],
  },
  {
    path: 'loyalty-gifts',
    name: 'admin.loyalty_gifts',
    meta: {
      title: 'loyalty::loyalty_gifts.loyalty_gifts',
      icon: 'tabler-gift',
    },
    children: [
      {
        path: '',
        name: 'admin.loyalty_gifts.index',
        component: () => import('@/modules/loyalty/pages/admin/gift/Index.vue'),
        meta: {
          permission: 'admin.loyalty_gifts.index',
        },
      },
    ],
  },
]

export default adminRoutes
