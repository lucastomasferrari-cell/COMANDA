<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import NavbarThemeSwitcher from '@/app/layouts/components/NavbarThemeSwitcher.vue'
  import UserProfile from '@/app/layouts/components/UserProfile/Index.vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  // Sprint 3.A.bis — header compacto 56px del PosLayout.
  // Reemplaza el DefaultLayoutWithVerticalNav navbar de 64px con
  // hamburguesa + botón chef-hat + botón POS + theme + user profile.
  // Se queda solo con lo esencial:
  //   izq:    logo chico COMANDA
  //   centro: breadcrumb (menú activo · caja) — text simple, no chips
  //   der:    toggle tema + avatar + botón "← Admin" (si hay permiso)

  const { t } = useI18n()
  const { can } = useAuth()
  const appStore = useAppStore()
  const router = useRouter()

  // Breadcrumb: lee info del POS viewer desde el appStore si existe un
  // contexto global de POS sesión. Como hoy no hay un store global para
  // eso, el breadcrumb se mantiene simple (solo app_name). El modo
  // específico (Salón/Mostrador/etc) ya se comunica por el switcher
  // lateral, no hace falta duplicarlo en el header.
  const appName = computed(() => appStore.appName || 'COMANDA')

  // Botón "← Admin" solo visible si el user tiene permiso al dashboard
  // admin. Cajeros puros y mozos no lo ven — van directo al POS tras
  // login y no tienen salida hacia el panel admin (decisión FILOSOFIA §7:
  // menos superficie para roles operativos).
  const canGoToAdmin = computed(() => can('admin.dashboard.view'))

  const goToAdmin = (): void => {
    router.push({ name: 'admin.dashboard' })
  }
</script>

<template>
  <header class="pos-header">
    <div class="pos-header__left">
      <RouterLink class="pos-header__logo" to="/admin/pos">
        <span class="pos-header__logo-text">{{ appName }}</span>
      </RouterLink>
    </div>

    <div class="pos-header__center">
      <!-- Breadcrumb simple. Sin chips, sin boxes — text only. -->
    </div>

    <div class="pos-header__right d-flex align-center ga-1">
      <NavbarThemeSwitcher />
      <UserProfile />
      <VBtn
        v-if="canGoToAdmin"
        class="pos-header__admin-btn ms-1"
        prepend-icon="tabler-arrow-left"
        size="small"
        variant="text"
        @click="goToAdmin"
      >
        {{ t('pos::pos_viewer.back_to_admin') }}
      </VBtn>
    </div>
  </header>
</template>

<style lang="scss" scoped>
/* Header 56px altura fija, sin elevación pesada — solo un hairline
   separador abajo contra el content. Padding horizontal 16px, vertical
   auto centrado vía align-items. */
.pos-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 56px;
  flex-shrink: 0;
  padding: 0 16px;
  background: rgb(var(--v-theme-surface));
  border-bottom: 1px solid rgb(var(--v-theme-border));
  gap: 16px;
}

.pos-header__left {
  display: flex;
  align-items: center;
  min-width: 0;
}

.pos-header__logo {
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  color: rgb(var(--v-theme-on-surface));
}

.pos-header__logo-text {
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: rgb(var(--v-theme-primary));
}

.pos-header__center {
  flex: 1 1 auto;
  min-width: 0;
  display: flex;
  justify-content: center;
  color: rgb(var(--v-theme-on-surface-variant));
  font-size: 0.875rem;
}

.pos-header__right {
  flex-shrink: 0;
}

.pos-header__admin-btn {
  color: rgb(var(--v-theme-on-surface-variant));
}
</style>
