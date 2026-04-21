<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    details: Record<string, any>
  }>()
  const { t } = useI18n()
  const isVisible = computed(() => props.details?.car_plate || props.details?.car_description || props.details?.scheduled_at)

</script>

<template>
  <VCol v-if="isVisible" cols="12">
    <VCard>
      <VCardTitle>
        {{ t('order::orders.show.cards.details') }}
      </VCardTitle>
      <VCardText>
        <div v-if="details?.car_plate" class="d-flex justify-space-between align-center">
          <span class="font-weight-bold">
            {{ t('order::orders.show.car_plate') }}:
          </span>
          {{ details?.car_plate }}
        </div>
        <div v-if="details?.car_description" class="mt-2">
          <p class="font-weight-bold mb-0">
            {{ t('order::orders.show.car_description') }}:
          </p>
          {{ details?.car_description }}
        </div>
        <div v-if="details?.scheduled_at" class="d-flex justify-space-between align-center">
          <span class="font-weight-bold">
            {{ t('order::orders.show.scheduled_at') }}:
          </span>
          {{ details?.scheduled_at }}
        </div>
      </VCardText>
    </VCard>
  </VCol>
</template>
