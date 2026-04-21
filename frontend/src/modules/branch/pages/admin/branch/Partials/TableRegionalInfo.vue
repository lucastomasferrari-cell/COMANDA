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
          icon="tabler-map-pin-cog"
        />
        &nbsp;
        {{ t('branch::branches.table.regional_info') }}
      </VChip>
    </template>

    <VCard class="pa-3" width="250">
      <div class="pa-2">
        <div class="d-flex align-center mb-2">
          <VIcon class="mr-2" icon="tabler-flag" size="18" />
          <span class="text-body-2">{{ item.country_name }}</span>
        </div>

        <div class="d-flex align-center mb-2">
          <VIcon class="mr-2" icon="tabler-currency-dollar" size="18" />
          <span class="text-body-2">{{ item.currency }}</span>
        </div>

        <div class="d-flex align-start">
          <VIcon class="mr-2 mt-1" icon="tabler-clock" size="18" />
          <span class="text-body-2">{{ item.timezone }}</span>
        </div>
      </div>
    </VCard>
  </VMenu>
</template>
