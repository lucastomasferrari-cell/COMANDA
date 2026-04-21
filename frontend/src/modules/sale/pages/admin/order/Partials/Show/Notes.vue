<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    notes?: string | null
  }>()
  const { t } = useI18n()

</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('order::orders.show.cards.notes') }}
    </VCardTitle>
    <VCardText>
      <p v-if="notes" style="white-space: pre-line;">
        {{ notes }}
      </p>
      <span v-else class="text-center d-block">
        {{ t('order::orders.show.no_notes_available') }}
      </span>
    </VCardText>
  </VCard>
</template>
