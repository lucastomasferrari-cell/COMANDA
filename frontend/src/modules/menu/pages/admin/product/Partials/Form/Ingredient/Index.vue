<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import draggable from 'vuedraggable'
  import Ingredient from './Ingredient.vue'

  const props = defineProps<{
    form: any
    currentLanguage: Record<string, any>
    meta: Record<string, any>
  }>()
  const { t } = useI18n()

  const addIngredient = () => {
    props.form.state.ingredients.push({
      ingredient_id: null,
      quantity: null,
      loss_pct: null,
      note: null,
    })
  }

</script>

<template>
  <VCard class="mt-3">
    <template v-if="props.form.state.ingredients.length>0">
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-salad" size="20" />
          <span>{{ t('product::products.form.cards.ingredients') }}</span>
        </div>
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
                :list="form.state.ingredients"
                tag="tbody"
              >
                <template #item="{ element: ingredient, index }">
                  <Ingredient :form="form" :index="index" :ingredient="ingredient" :meta="meta" />
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
    <VCardText v-else class="text-center">
      <VIcon class="mb-3 text-medium-emphasis" color="primary" icon="tabler-salad" size="60" />
      <h3 class="text-h6 font-weight-bold mb-2">
        {{ t('product::products.form.no_ingredients_added') }}
      </h3>
      <p class="text-body-2 text-medium-emphasis mb-4">
        {{ t('product::products.form.no_ingredients_added_message') }}
      </p>
      <VBtn color="secondary" @click="addIngredient">
        <VIcon icon="tabler-playlist-add" start />
        {{ t('product::products.form.add_ingredient') }}
      </VBtn>
    </VCardText>
  </VCard>

</template>
