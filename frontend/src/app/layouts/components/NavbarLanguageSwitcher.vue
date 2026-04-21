<script lang="ts" setup>

import {useI18n} from 'vue-i18n'
import {useAppStore} from "@/modules/core/stores/appStore.ts";

const appStore = useAppStore()

const {locale} = useI18n()

const changeLanguage = (val: string) => {
  appStore.setCurrentLocale(val)
  location.reload()
}

</script>

<template>
  <VSelect
    v-model="locale"
    :items="appStore.supportedLanguages"
    :menu-props="{ contentClass: 'lang-dropdown'}"
    class="lang-select"
    density="compact"
    flat
    hide-details
    item-title="name"
    item-value="id"
    variant="solo"
    @update:model-value="changeLanguage"
  >
    <template #prepend-inner>
      <VIcon icon="tabler-language"/>
    </template>
    <template #selection="{ item }">
      <span class="text-uppercase font-weight-bold">{{ item?.value }}</span>
    </template>
  </VSelect>
</template>
<style>
.lang-select {
  border-radius: 15px;
  border: 1px solid #ededed;
  max-width: 100px;
}

.lang-select .v-input__control .v-field {
  border-radius: 15px;
}
</style>
