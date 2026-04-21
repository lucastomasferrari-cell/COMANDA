<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    item: Record<string, any>
    form: Record<string, any>
    index: number
    currency: string
    ingredients: Record<string, any>[]
  }>()

  const { t } = useI18n()
  const lineTotal = computed(() => props.item.quantity * props.item.unit_cost)

  const removeItem = () => {
    props.form.state.items.splice(props.index, 1)
  }

  const symbol = computed(() => {
    if (props.item.ingredient_id) {
      const ingredient = props.ingredients.find((item: Record<string, any>) => item.id === props.item.ingredient_id)
      if (ingredient) {
        return ingredient.symbol
      }
    }
    return ''
  })
</script>

<template>
  <td>
    <VSelect
      v-model="item.ingredient_id"
      :error="!!form.errors.value?.[`items.${index}.ingredient_id`]"
      :error-messages="form.errors.value?.[`items.${index}.ingredient_id`]"
      item-title="name"
      item-value="id"
      :items="ingredients"
      :placeholder="t('inventory::attributes.purchases.items.*.ingredient_id')"
    />
  </td>
  <td>
    <VTextField
      v-model.number="item.quantity"
      :error="!!form.errors.value?.[`items.${index}.quantity`]"
      :error-messages="form.errors.value?.[`items.${index}.quantity`]"
      min="0"
      :placeholder="t('inventory::attributes.purchases.items.*.quantity')"
      step="0.01"
    >
      <template v-if="symbol" #append-inner>
        {{ symbol }}
      </template>
    </VTextField>
  </td>
  <td>
    <VTextField
      v-model.number="item.unit_cost"
      :error="!!form.errors.value?.[`items.${index}.unit_cost`]"
      :error-messages="form.errors.value?.[`items.${index}.unit_cost`]"
      min="0"
      :placeholder="t('inventory::attributes.purchases.items.*.unit_cost')"
      step="0.01"
    >
      <template #prepend-inner>
        {{ currency }}
      </template>
    </VTextField>
  </td>
  <td class="text-center">
    <span class="font-weight-bold">
      {{ currency }} {{ lineTotal }}
    </span>
  </td>
  <td class="text-center">
    <VBtn
      color="error"
      :disabled="form.state.items.length === 1"
      icon="tabler-trash"
      variant="text"
      @click="removeItem"
    />
  </td>
</template>
