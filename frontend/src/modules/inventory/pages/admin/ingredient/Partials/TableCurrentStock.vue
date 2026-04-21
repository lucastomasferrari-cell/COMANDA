<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{ item: Record<string, any> }>()
  const { t } = useI18n()

  const isAbove = computed(() => props.item.current_stock > props.item.alert_quantity)
</script>

<template>
  <VTooltip
    :text="t(`inventory::ingredients.tooltips.${isAbove?'current_stock_is_above_the_alert_threshold':'current_stock_has_fallen_below_the_alert_threshold'}`)"
  >
    <template #activator="{ props }">
      <VChip
        :color="isAbove? 'success' : 'error'"
        size="small"
        v-bind="props"
      >
        {{ item.current_stock }}
      </VChip>
    </template>
  </VTooltip>
</template>
