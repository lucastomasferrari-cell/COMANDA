<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{ item: any }>()
  const { t } = useI18n()
</script>

<template>
  <VTooltip v-if="item.is_global" location="top">
    <template #activator="{ props }">
      <div class="d-flex align-center gap-1" v-bind="props">
        <VIcon
          color="primary"
          icon="tabler-rosette-discount-check-filled"
        />
        {{ item.name }}
      </div>
    </template>
    <span>{{ t('tax::taxes.tooltips.global_tax') }}</span>
  </VTooltip>
  <span v-else>{{ item.name }}</span>
</template>
