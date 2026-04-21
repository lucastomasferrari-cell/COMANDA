<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    item: Record<string, any>
  }>()
  const { t } = useI18n()

</script>

<template>
  <VCard
    v-for="receipt in item.receipts"
    :key="receipt.id"
    class="pa-4 mb-3"
    elevation="1"
    rounded
  >
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-4">
      <div class="text-subtitle-1 font-weight-bold">
        {{ t('inventory::purchases.show.receipt') }} #{{ receipt.id }}
      </div>
      <div class="text-caption text-grey-darken-1">
        {{ receipt.received_at }} • {{ receipt.received_by.name }}
      </div>
    </div>

    <div
      v-if="receipt.reference|| receipt.notes"
      class="text-body-2 mb-4"
    >
      <VChip
        v-if="receipt.reference"
        class="mr-2"
        color="info"
        label
        size="small"
        variant="tonal"
      >
        {{ t('inventory::purchases.show.ref') }}: {{ receipt.reference }}
      </VChip>
      <p v-if="receipt.notes" class="text-grey-darken-1 mt-3"> {{ receipt.notes }}</p>
    </div>

    <VList class="pa-0" density="compact" lines="two">
      <VListItem
        v-for="receiptItem in receipt.items"
        :key="receiptItem.id"
        class="px-0"
      >
        <template #prepend>
          <VAvatar

            color="primary"
            size="35"
            variant="tonal"
          >
            <small class="font-weight-bold">
              {{ receiptItem.item.ingredient.symbol }}
            </small>
          </VAvatar>
        </template>
        <VListItemTitle class="text-body-1 font-weight-medium">
          {{ receiptItem.item.ingredient.name }}
        </VListItemTitle>
        <VListItemSubtitle class="text-caption">
          {{ t('inventory::purchases.show.quantity') }}: {{ receiptItem.received_quantity }}
          {{ receiptItem.item.ingredient.symbol }}
        </VListItemSubtitle>
      </VListItem>
    </VList>
  </VCard>

</template>
