<script lang="ts" setup>
  import { computed } from 'vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const { user, logout } = useAuth()

  const displayName = computed(() => user?.name ?? user?.username ?? 'Usuario')
  const jobLabel = computed(() => {
    // Por ahora usamos el rol como "job activo". Cuando tengamos
    // el modulo Jobs, reemplazamos con el job real del user.
    return user?.role?.display_name ?? user?.role?.name ?? ''
  })
</script>

<template>
  <VMenu :close-on-content-click="false" offset="4">
    <template #activator="{ props: activator }">
      <VBtn
        v-bind="activator"
        variant="text"
        class="navbar-cashier px-2"
      >
        <VAvatar color="primary" size="32" class="me-2">
          <span class="text-white text-caption font-weight-bold">
            {{ displayName.charAt(0).toUpperCase() }}
          </span>
        </VAvatar>
        <div class="d-flex flex-column align-start" style="line-height: 1;">
          <span class="text-body-2 font-weight-medium">{{ displayName }}</span>
          <span v-if="jobLabel" class="text-caption text-medium-emphasis">{{ jobLabel }}</span>
        </div>
        <VIcon icon="tabler-chevron-down" size="16" class="ms-2" />
      </VBtn>
    </template>

    <VList density="compact" min-width="220">
      <VListItem disabled>
        <template #prepend>
          <VIcon icon="tabler-briefcase" />
        </template>
        <VListItemTitle>Cambiar de job</VListItemTitle>
        <VListItemSubtitle>Próximamente</VListItemSubtitle>
      </VListItem>
      <VListItem disabled>
        <template #prepend>
          <VIcon icon="tabler-clock-off" />
        </template>
        <VListItemTitle>Cerrar turno</VListItemTitle>
        <VListItemSubtitle>Próximamente</VListItemSubtitle>
      </VListItem>
      <VDivider class="my-1" />
      <VListItem @click="logout">
        <template #prepend>
          <VIcon icon="tabler-logout" color="error" />
        </template>
        <VListItemTitle class="text-error">Cerrar sesión</VListItemTitle>
      </VListItem>
    </VList>
  </VMenu>
</template>

<style scoped>
.navbar-cashier {
  text-transform: none;
}
</style>
