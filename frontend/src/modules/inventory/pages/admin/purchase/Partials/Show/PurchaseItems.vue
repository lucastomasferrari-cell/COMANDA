<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    item: Record<string, any>
  }>()
  const { t } = useI18n()

</script>

<template>
  <VCard class="mt-5">
    <VCardTitle>
      {{ t('inventory::purchases.show.cards.purchase_items') }}
    </VCardTitle>
    <VCardText>
      <v-table class="table-items">
        <thead>
          <tr>
            <th style="width: 7%">#</th>
            <th>
              {{ t('inventory::purchases.show.ingredient') }}
            </th>
            <th>
              {{ t('inventory::purchases.show.quantity') }}
            </th>
            <th>
              {{ t('inventory::purchases.show.received_quantity') }}
            </th>
            <th>
              {{ t('inventory::purchases.show.unit_cost') }}
            </th>
            <th>
              {{ t('inventory::purchases.form.line_total') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(purchaseItem,index) in item.items" :key="purchaseItem.id">
            <td>{{ Number(index) + 1 }}</td>
            <td>{{ purchaseItem.ingredient.name }}</td>
            <td>{{ purchaseItem.quantity }} {{ purchaseItem.ingredient.unit.symbol }}</td>
            <td>{{ purchaseItem.received_quantity }} {{ purchaseItem.ingredient.unit.symbol }}</td>
            <td>
              <Money :money="purchaseItem.unit_cost" />
            </td>
            <td>
              <Money :money="purchaseItem.line_total" />
            </td>
          </tr>
        </tbody>
      </v-table>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.table-items th {
  text-transform: capitalize;
  font-weight: bold !important;
}
</style>
