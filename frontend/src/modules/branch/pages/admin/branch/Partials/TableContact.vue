<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{ item: any }>()

  const { t } = useI18n()
</script>

<template>
  <VMenu
    location="top"
    offset="8"
  >
    <template #activator="{ props }">
      <VChip
        class="cursor-pointer"
        color="default"
        size="small"
        v-bind="props"
      >
        <VIcon
          class="cursor-pointer text-medium-emphasis"
          icon="tabler-address-book"
        />
        &nbsp;
        {{ t('branch::branches.table.contact_info') }}
      </VChip>
    </template>

    <VCard class="pa-2" max-width="320" width="300">
      <div
        v-if="item.email || item.phone ||item.address"
        class="pa-2"
      >
        <div v-if="item.email" class="d-flex align-center mb-2">
          <VIcon class="mr-2" icon="tabler-mail" size="18" />
          <span class="text-body-2">{{ item.email }}</span>
        </div>

        <div v-if="item.phone" class="d-flex align-center mb-2">
          <VIcon class="mr-2" icon="tabler-phone" size="18" />
          <span class="text-body-2">{{ item.phone }}</span>
        </div>

        <div v-if="item.address" class="d-flex align-start">
          <VIcon class="mr-2 mt-1" icon="tabler-map-pin" size="18" />
          <span class="text-body-2">{{ item.address }}</span>
        </div>
      </div>
      <div v-else class="text-caption text-center py-4">
        {{ t('branch::branches.contact_not_available') }}
      </div>
    </VCard>
  </VMenu>
</template>
