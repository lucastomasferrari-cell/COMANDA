<script lang="ts" setup>

  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import NavbarAction from '@/app/layouts/components/NavbarAction.vue'

  const { t } = useI18n()
  const router = useRouter()

  function goToPos () {
    router.push({
      name: 'admin.pos.index',
      params: { cartId: crypto.randomUUID() },
    } as unknown as RouteLocationRaw)
  }

</script>

<template>
  <VTooltip>
    <template #activator="{ props }">
      <NavbarAction v-bind="props">
        <VBtn color="default" icon="tabler-cash-register" variant="text" @click="goToPos" />
      </NavbarAction>
    </template>
    <span>
      {{ t('pos::pos.pos_viewer') }}
    </span>
  </VTooltip>
</template>
