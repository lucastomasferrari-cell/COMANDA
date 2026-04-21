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
      {{ t('seatingplan::tables.show.cards.table_details') }}
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('seatingplan::tables.show.name')" :value="item.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('seatingplan::tables.show.branch')" :value="item.branch.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('seatingplan::tables.show.floor')" :value="item.floor.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('seatingplan::tables.show.zone')" :value="item.zone.name" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('seatingplan::tables.show.capacity')" :value="item.capacity" />
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('seatingplan::tables.show.status')">
            <TableStatus :item="item" />
          </BlockInfo>
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo :title="t('seatingplan::tables.show.shape')">
            <TableEnum column="shape" :item="item" />
          </BlockInfo>
        </VCol>
        <VCol cols="4" md="2">
          <BlockInfo
            :title="t('seatingplan::tables.show.activated')"
            :value="t(`seatingplan::tables.show.${item.is_active?'yes':'no'}`)"
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
