<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    form: any
    ingredient: any
    index: number
    meta: Record<string, any>
  }>()
  const { t } = useI18n()

  const removeIngredient = (index: number) => {
    props.form.state.ingredients.splice(index, 1)
  }

  const symbol = computed(() => {
    if (props.ingredient.ingredient_id) {
      const ingredient = props.meta.ingredients.find((item: Record<string, any>) => item.id === props.ingredient.ingredient_id)
      if (ingredient) {
        return ingredient.symbol
      }
    }
    return ''
  })

</script>

<template>
  <tr>
    <td class="text-medium-emphasis">
      <VIcon class="cursor-move drag-handle" icon="tabler-grip-vertical" />
    </td>
    <td>
      <VSelect
        v-model="ingredient.ingredient_id"
        :error="!!form.errors.value?.[`ingredients.${index}.ingredient_id`]"
        :error-messages="form.errors.value?.[`ingredients.${index}.ingredient_id`]"
        item-title="name"
        item-value="id"
        :items="meta.ingredients"
        :placeholder="t('product::attributes.products.ingredients.*.ingredient_id')"
      />
    </td>
    <td>
      <VTextField
        v-model="ingredient.quantity"
        :error="!!form.errors.value?.[`ingredients.${index}.quantity`]"
        :error-messages="form.errors.value?.[`ingredients.${index}.quantity`]"
        min="0"
        :placeholder="t('product::attributes.products.ingredients.*.quantity')"
        step="0.01"
      >
        <template v-if="symbol" #append-inner>
          {{ symbol }}
        </template>
      </VTextField>
    </td>
    <td>
      <VTextField
        v-model="ingredient.loss_pct"
        :error="!!form.errors.value?.[`ingredients.${index}.loss_pct`]"
        :error-messages="form.errors.value?.[`ingredients.${index}.loss_pct`]"
        min="0"
        :placeholder="t('product::attributes.products.ingredients.*.loss_pct')"
        step="0.01"
      >
        <template #append-inner>
          %
        </template>
      </VTextField>
    </td>
    <td>
      <VTextField
        v-model="ingredient.note"
        :error="!!form.errors.value?.[`ingredients.${index}.note`]"
        :error-messages="form.errors.value?.[`ingredients.${index}.note`]"
        :placeholder="t('product::attributes.products.ingredients.*.note')"
      />
    </td>
    <td>
      <VBtn
        color="error"
        icon
        size="small"
        variant="text"
        @click="removeIngredient(index)"
      >
        <VIcon icon="tabler-trash" />
      </VBtn>
    </td>
  </tr>
</template>
