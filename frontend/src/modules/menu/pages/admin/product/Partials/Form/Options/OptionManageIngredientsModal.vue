<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import draggable from 'vuedraggable'
  import Ingredient from './Ingredient.vue'

  const props = defineProps<{
    modelValue: boolean
    value: any
    index: number
    optionIndex: number
    form: any
    meta: Record<string, any>
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const open = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val),
  })

  const { t } = useI18n()

  const addIngredient = () => {
    props.value.ingredients.push({
      ingredient_id: null,
      quantity: null,
      operation: 'add',
      loss_pct: null,
      note: null,
    })
  }
</script>

<template>
  <VDialog v-model="open" max-width="1100" min-height="300">
    <VCard>
      <template v-if="props.value.ingredients.length>0">
        <VCardTitle class="d-flex align-center justify-space-between">
          <div class="d-flex align-center gap-2">
            <VIcon color="primary" icon="tabler-salad" />
            <span>{{ t('product::products.form.option_manage_ingredients_modal.title') }}</span>
          </div>
          <VBtn icon variant="text" @click="open = false">
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>
        <VCardText>
          <VRow>
            <VCol cols="12">
              <table class="text-center bordered-table">
                <thead>
                  <tr>
                    <th style="width: 5%" />
                    <th>{{ t('product::products.form.ingredient') }}</th>
                    <th>{{ t('product::products.form.quantity') }}</th>
                    <th>{{ t('product::products.form.loss_pct') }}</th>
                    <th>{{ t('product::products.form.operation') }}</th>
                    <th>{{ t('product::products.form.note') }}</th>
                    <th style="width: 5%" />
                  </tr>
                </thead>
                <draggable
                  animation="180"
                  chosen-class="drag-chosen"
                  drag-class="drag-dragging"
                  ghost-class="drag-ghost"
                  handle=".drag-handle"
                  item-key="id"
                  :list="value.ingredients"
                  tag="tbody"
                >
                  <template #item="{ element: ingredient, index:ingredientIndex }">
                    <Ingredient
                      :form="form"
                      :index="ingredientIndex"
                      :ingredient="ingredient"
                      :meta="meta"
                      :option-index="optionIndex"
                      :value="value"
                      :value-index="index"
                    />
                  </template>
                </draggable>
              </table>
            </VCol>
            <VCol cols="12">
              <VBtn color="secondary" @click="addIngredient">
                <VIcon icon="tabler-playlist-add" start />
                {{ t('product::products.form.add_ingredient') }}
              </VBtn>
            </VCol>
          </VRow>
        </VCardText>
      </template>
      <VCardText
        v-else
        class="d-flex flex-column align-center justify-center text-center py-8"
      >
        <VIcon
          class="mb-3 text-medium-emphasis"
          color="primary"
          icon="tabler-salad"
          size="60"
        />

        <h3 class="text-h6 font-weight-bold mb-2">
          {{ t('product::products.form.no_ingredients_added') }}
        </h3>

        <p class="text-body-2 text-medium-emphasis mb-4" style="max-width: 600px;">
          {{ t('product::products.form.option_manage_ingredients_modal.description') }}
        </p>

        <VBtn color="secondary" @click="addIngredient">
          <VIcon icon="tabler-playlist-add" start />
          {{ t('product::products.form.add_ingredient') }}
        </VBtn>
      </VCardText>
    </VCard>
  </vdialog>
</template>

<style lang="scss" scoped>

</style>
