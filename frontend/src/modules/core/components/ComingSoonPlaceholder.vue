<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    title?: string
  }>()

  const { t } = useI18n()
</script>

<template>
  <VCard variant="flat" class="mx-auto mt-8" max-width="640">
    <VCardText class="text-center py-12">
      <VIcon
        icon="tabler-clock-hour-4"
        size="72"
        color="primary"
        class="mb-6"
      />
      <h2 class="text-h5 mb-3 font-weight-medium">
        {{ title ?? t('admin::sidebar.coming_soon_title') }}
      </h2>
      <p class="text-body-1 text-medium-emphasis">
        {{ t('admin::sidebar.coming_soon_description') }}
      </p>
    </VCardText>
  </VCard>
</template>
