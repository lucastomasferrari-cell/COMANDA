<script lang="ts" setup>

import type {RouteLocationRaw} from 'vue-router'
import {useI18n} from 'vue-i18n'
import NavbarAction from "@/app/layouts/components/NavbarAction.vue";

const {t} = useI18n()
const router = useRouter()
const goToKitchen = () => {
  router.push({name: 'admin.pos.kitchen_viewer'} as unknown as RouteLocationRaw)
}

</script>

<template>
  <VTooltip>
    <template #activator="{ props }">
      <NavbarAction v-bind="props">
        <VBtn color='default' icon="tabler-chef-hat" variant='text' @click="goToKitchen"/>
      </NavbarAction>
    </template>
    <span>
      {{ t('pos::pos.kitchen_viewer') }}
    </span>
  </VTooltip>
</template>
