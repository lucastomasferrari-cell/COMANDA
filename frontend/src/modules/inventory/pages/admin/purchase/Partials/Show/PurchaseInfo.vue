<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    item: Record<string, any>
  }>()
  const { t } = useI18n()

</script>

<template>
  <VCard>
    <VCardTitle>
      {{ t('inventory::purchases.show.cards.purchase_information') }}
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.reference_number')" :value="item.reference_number" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.branch')" :value="item.branch.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.supplier')" :value="item.supplier.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.status')">
            <TableStatus :item="item" />
          </BlockInfo>
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.expected_at')" :value="item.expected_at" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.updated_at')" :value="item.updated_at" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.created_at')" :value="item.created_at" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.notes')" :value="item.notes" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.sub_total')">
            <Money :money="item.sub_total" />
          </BlockInfo>
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.tax')">
            <Money :money="item.tax" />
          </BlockInfo>
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.discount')">
            <Money :money="item.discount" />
          </BlockInfo>
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('inventory::purchases.show.total')">
            <Money :money="item.total" />
          </BlockInfo>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
