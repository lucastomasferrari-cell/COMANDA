<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{ item: any }>()

  const { t } = useI18n()
</script>

<template>
  <div class="d-flex align-center gap-3 text-caption text-medium-emphasis">
    <VTooltip location="top">
      <template #activator="{ props }">
        <VIcon
          class="mr-1"
          :icon="item.agent.desktop ? 'tabler-device-desktop' : 'tabler-device-mobile'"
          size="18"
          v-bind="props"
        />
        <span v-bind="props">{{ item.agent.desktop ? t('admin::admin.table.yes') : t('admin::admin.table.no') }}</span>
      </template>
      <span>{{ t('admin::admin.table.from_desktop') }}</span>
    </VTooltip>

    <VTooltip location="top">
      <template #activator="{ props }">
        <VIcon class="ml-4 mr-1" icon="tabler-brand-windows" size="18" v-bind="props" />
        <span v-bind="props">{{ item.agent.platform || t('admin::admin.table.unknown') }}</span>
      </template>
      <span>{{ t('admin::admin.table.platform') }}</span>
    </VTooltip>

    <VTooltip location="top">
      <template #activator="{ props }">
        <VIcon class="ml-4 mr-1" icon="tabler-world-www" size="18" v-bind="props" />
        <span v-bind="props">{{ item.agent.browser || t('admin::admin.table.unknown') }}</span>
      </template>
      <span>{{ t('admin::admin.table.browser') }}</span>
    </VTooltip>
  </div>
</template>
