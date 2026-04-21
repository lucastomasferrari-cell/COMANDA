import type { RouteRecordRaw } from 'vue-router'

const adminRoutes: RouteRecordRaw[] = [
  {
    path: 'settings',
    name: 'admin.settings.edit',
    redirect: { name: 'admin.settings.general' },
    component: () => import('@/modules/setting/pages/admin/Layout.vue'),
    meta: { pageHeader: false },
    children: [
      {
        path: 'general',
        name: 'admin.settings.general',
        component: () => import('@/modules/setting/pages/admin/setting/General.vue'),
        meta: {
          title: 'setting::settings.sections.general',
          permission: 'admin.settings.edit',
        },
      },
      {
        path: 'application',
        name: 'admin.settings.application',
        component: () => import('@/modules/setting/pages/admin/setting/Application.vue'),
        meta: {
          title: 'setting::settings.sections.application',
          permission: 'admin.settings.edit',
        },
      },
      {
        path: 'logo',
        name: 'admin.settings.logo',
        component: () => import('@/modules/setting/pages/admin/setting/Logo.vue'),
        meta: {
          title: 'setting::settings.sections.logo',
          permission: 'admin.settings.edit',
        },
      },
      {
        path: 'appearance',
        name: 'admin.settings.appearance',
        component: () => import('@/modules/setting/pages/admin/setting/Appearance.vue'),
        meta: {
          title: 'setting::settings.sections.appearance',
          permission: 'admin.settings.edit',
        },
      },
      {
        path: 'pwa',
        name: 'admin.settings.pwa',
        component: () => import('@/modules/setting/pages/admin/setting/Pwa.vue'),
        meta: {
          title: 'setting::settings.sections.pwa',
          permission: 'admin.settings.edit',
        },
      },
      {
        path: 'kitchen',
        name: 'admin.settings.kitchen',
        component: () => import('@/modules/setting/pages/admin/setting/Kitchen.vue'),
        meta: {
          title: 'setting::settings.sections.kitchen',
          permission: 'admin.settings.edit',
        },
      },
      {
        path: 'currency',
        name: 'admin.settings.currency',
        component: () => import('@/modules/setting/pages/admin/setting/Currency.vue'),
        meta: {
          title: 'setting::settings.sections.currency',
          permission: 'admin.settings.edit',
        },
      },
      {
        path: 'mail',
        name: 'admin.settings.mail',
        component: () => import('@/modules/setting/pages/admin/setting/Mail.vue'),
        meta: {
          title: 'setting::settings.sections.mail',
          permission: 'admin.settings.edit',
        },
      },
      {
        path: 'filesystem',
        name: 'admin.settings.filesystem',
        component: () => import('@/modules/setting/pages/admin/setting/Filesystem.vue'),
        meta: {
          title: 'setting::settings.sections.filesystem',
          permission: 'admin.settings.edit',
        },
      },
    ],
  },
]

export default adminRoutes
