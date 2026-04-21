<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import Item from './Item.vue'

  const props = defineProps<{
    form: Record<string, any>
    currency: string
    ingredients: Record<string, any>[]
  }>()

  const { t } = useI18n()

  const addItem = () => {
    props.form.state.items.push({
      ingredient_id: null,
      quantity: null,
      unit_cost: null,
    })
  }

</script>

<template>
  <table class="w-100 bordered-table">
    <thead>
      <tr>
        <th>
          {{ t('inventory::attributes.purchases.items.*.ingredient_id') }}
        </th>
        <th>
          {{ t('inventory::attributes.purchases.items.*.quantity') }}
        </th>
        <th>
          {{ t('inventory::attributes.purchases.items.*.unit_cost') }}
          ({{ currency }})
        </th>
        <th>
          {{ t('inventory::purchases.form.line_total') }}
        </th>
        <th style="width: 10%;">
          {{ t('admin::admin.table.actions') }}
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(item,index) in form.state.items" :key="index">
        <Item
          :key="index"
          :currency="currency"
          :form="form"
          :index="Number(index)"
          :ingredients="ingredients"
          :item="item"
        />
      </tr>
    </tbody>
  </table>
  <VRow class="mt-2">
    <VCol class="text-end">
      <VBtn color="secondary" prepend-icon="tabler-playlist-add" @click="addItem">
        {{ t('inventory::purchases.form.add_item') }}
      </VBtn>
    </VCol>
  </VRow>
</template>
