<script lang="ts" setup>

  import type { TargetName } from '@/modules/core/contracts/Target.ts'
  import Footer from '@/app/layouts/components/Footer.vue'
  import HeaderPage from '@/app/layouts/components/HeaderPage.vue'
  // KitchenViewer (chef-hat) sacado del topbar en bloque 7: el topbar queda
  // con un solo shortcut (cash-register → POS). El KDS sigue accesible por
  // URL directa /admin/kds (alias definido en admin.routes.ts).
  // import KitchenViewer from '@/app/layouts/components/KitchenViewer.vue'
  import NavbarAction from '@/app/layouts/components/NavbarAction.vue'
  import NavbarFullscreenSwitcher from '@/app/layouts/components/NavbarFullscreenSwitcher.vue'
  // Selector de idioma desmontado: COMANDA es mercado argentino, el
  // locale queda fijo en es_AR (ver backend .env APP_LOCALE). Si en el
  // futuro necesitamos multi-idioma para franquicias, descomentar esta
  // línea y el tag <NavbarLanguageSwitcher /> más abajo.
  // import NavbarLanguageSwitcher from '@/app/layouts/components/NavbarLanguageSwitcher.vue'
  import NavbarThemeSwitcher from '@/app/layouts/components/NavbarThemeSwitcher.vue'
  import NavbarViewPos from '@/app/layouts/components/NavbarViewPos.vue'
  import NavItems from '@/app/layouts/components/NavItems.vue'
  import NavbarAntifraudAlerts from '@/app/layouts/components/NavbarAntifraudAlerts.vue'
  import UserProfile from '@/app/layouts/components/UserProfile/Index.vue'
  import VerticalNavLayout from '@/app/layouts/components/VerticalNavLayout.vue'
  import GlobalManagerPinDialog from '@/modules/auth/components/GlobalManagerPinDialog.vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  defineProps<{
    target: TargetName
  }>()

  const appStore = useAppStore()
  const { can } = useAuth()
  const route = useRoute()
  const sidebarLgNone = computed(() => !['admin.pos.kitchen_viewer', 'admin.pos.index'].includes((route.name as string)))
  const isPosScreen = computed(() => ['admin.pos.index', 'admin.pos.customer_viewer', 'admin.pos.kitchen_viewer'].includes((route.name as string)))
</script>

<template>
  <VerticalNavLayout :class="{'pos-layout': isPosScreen}">
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <div id="main-header-left-content" class="d-flex align-center w-50 gap-3">
          <NavbarAction :class="{'d-lg-none':sidebarLgNone}">
            <VBtn
              color="default"
              icon="tabler-menu-2"
              variant="text"
              @click="toggleVerticalOverlayNavActive(true)"
            />
          </NavbarAction>
        </div>

        <VSpacer />
        <template
          v-if="!['admin.pos.kitchen_viewer','admin.pos.index'].includes((route?.name as string))"
        >
          <NavbarViewPos v-if="can('admin.pos.index')" class="me-2" />
        </template>
        <NavbarAntifraudAlerts class="me-2" />
        <NavbarFullscreenSwitcher class="me-2" />
        <NavbarThemeSwitcher class="me-2" />
        <!-- <NavbarLanguageSwitcher class="me-2" /> -->
        <UserProfile />
      </div>

    </template>

    <template #vertical-nav-header="{ toggleIsOverlayNavActive }">
      <RouterLink
        class="app-logo app-title-wrapper"
        to="/admin"
      >
        <h1 class="app-logo-title">
          {{ appStore.appName }}
        </h1>
      </RouterLink>
      <VBtn
        class="d-block"
        :class="{'d-lg-none':sidebarLgNone}"
        color="default"
        icon="bx-x"
        variant="text"
        @click="toggleIsOverlayNavActive(false)"
      />
    </template>
    <template #vertical-nav-content>
      <NavItems :target="target" />
    </template>
    <HeaderPage />
    <slot />
    <template v-if="(route?.name as string) !='admin.pos.index'" #footer>
      <Footer />
    </template>
  </VerticalNavLayout>

  <!-- Singleton global: el axios interceptor dispara esto en 403
       con code=manager_approval_required. Cualquier dialog o UI
       que llame al store también lo muestra. -->
  <GlobalManagerPinDialog />
</template>

<style lang="scss" scoped>
.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}

.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;

  .app-logo-title {
    font-size: 1.25rem;
    font-weight: 500;
    line-height: 1.75rem;
    text-transform: uppercase;
  }
}
</style>
