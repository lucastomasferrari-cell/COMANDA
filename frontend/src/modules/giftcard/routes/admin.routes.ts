import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'gift-cards-analytics',
    name: 'admin.gift_cards_analytics',
    meta: {
      title: 'giftcard::gift_cards.analytics.dashboard',
      icon: 'tabler-chart-histogram',
    },
    children: [
      {
        path: '',
        name: 'admin.gift_cards_analytics.index',
        component: () => import('@/modules/giftcard/pages/admin/analytics/Index.vue'),
        meta: {
          title: 'giftcard::gift_cards.analytics.dashboard',
          permission: 'admin.gift_cards.analytics',
        },
      },
    ],
  },
  {
    path: 'gift-cards',
    name: 'admin.gift_cards',
    meta: {
      title: 'giftcard::gift_cards.gift_cards',
      icon: 'tabler-gift-card',
    },
    children: [
      {
        path: '',
        name: 'admin.gift_cards.index',
        component: () => import('@/modules/giftcard/pages/admin/giftCard/Index.vue'),
        meta: {
          permission: 'admin.gift_cards.index',
        },
      },
      {
        path: ':id/show',
        name: 'admin.gift_cards.show',
        component: () => import('@/modules/giftcard/pages/admin/giftCard/Show.vue'),
        meta: {
          title: 'admin::resource.show',
          transParam: { resource: 'giftcard::gift_cards.gift_card' },
          permission: 'admin.gift_cards.show',
        },
      },
      {
        path: 'create',
        name: 'admin.gift_cards.create',
        component: () => import('@/modules/giftcard/pages/admin/giftCard/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'giftcard::gift_cards.gift_card' },
          permission: 'admin.gift_cards.create',
        },
      },
      {
        path: ':id/edit',
        name: 'admin.gift_cards.edit',
        component: () => import('@/modules/giftcard/pages/admin/giftCard/Edit.vue'),
        meta: {
          title: 'admin::resource.edit',
          transParam: { resource: 'giftcard::gift_cards.gift_card' },
          permission: 'admin.gift_cards.edit',
        },
      },
    ],
  },
  {
    path: 'gift-card-transactions',
    name: 'admin.gift_card_transactions',
    meta: {
      title: 'giftcard::gift_card_transactions.gift_card_transactions',
      icon: 'tabler-arrows-exchange',
    },
    children: [
      {
        path: '',
        name: 'admin.gift_card_transactions.index',
        component: () => import('@/modules/giftcard/pages/admin/giftCardTransaction/Index.vue'),
        meta: {
          permission: 'admin.gift_card_transactions.index',
        },
      },
      {
        path: ':id/show',
        name: 'admin.gift_card_transactions.show',
        component: () => import('@/modules/giftcard/pages/admin/giftCardTransaction/Show.vue'),
        meta: {
          title: 'admin::resource.show',
          transParam: { resource: 'giftcard::gift_card_transactions.gift_card_transaction' },
          permission: 'admin.gift_card_transactions.show',
        },
      },
    ],
  },
  {
    path: 'gift-card-batches',
    name: 'admin.gift_card_batches',
    meta: {
      title: 'giftcard::gift_card_batches.gift_card_batches',
      icon: 'tabler-stack-2',
    },
    children: [
      {
        path: '',
        name: 'admin.gift_card_batches.index',
        component: () => import('@/modules/giftcard/pages/admin/giftCardBatch/Index.vue'),
        meta: {
          permission: 'admin.gift_card_batches.index',
        },
      },
      {
        path: 'create',
        name: 'admin.gift_card_batches.create',
        component: () => import('@/modules/giftcard/pages/admin/giftCardBatch/Create.vue'),
        meta: {
          title: 'admin::resource.create',
          transParam: { resource: 'giftcard::gift_card_batches.gift_card_batch' },
          permission: 'admin.gift_card_batches.create',
        },
      },
    ],
  },
]

export default adminRoutes
