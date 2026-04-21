<script lang="ts" setup>
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import draggable from 'vuedraggable'
  import OptionManageIngredientsModal from './OptionManageIngredientsModal.vue'

  const props = defineProps<{
    form: any
    option: any
    optionIndex: number
    meta: Record<string, any>
    currentLanguage: Record<string, any>
    isMultipleOptions: boolean
    currency: string
  }>()

  const { t } = useI18n()

  const openManageIngredientsModal = ref(false)
  const optionValue = ref(null)
  const optionValueIndex = ref<number>(0)
  const addRow = () => {
    props.option.values.push({
      label: {},
      price: null,
      price_type: 'fixed',
      ingredients: [],
    })
  }

  const removeRow = (index: number) => {
    props.option.values.splice(index, 1)
  }

  const columnWidth = computed(() => (props.isMultipleOptions ? '20%' : '50%'))

  const manageIngredients = (index: number, value: any) => {
    optionValue.value = value
    optionValueIndex.value = index
    openManageIngredientsModal.value = true
  }
</script>

<template>
  <VRow v-if="option.type">
    <VCol cols="12">
      <table class="text-center bordered-table">
        <thead>
          <tr>
            <th v-if="isMultipleOptions" style="width: 5%" />
            <th v-if="isMultipleOptions" :style="{ width: columnWidth }">{{ t('product::products.form.label') }}</th>
            <th :style="{ width: columnWidth }">{{ t('product::products.form.price') }}</th>
            <th :style="{ width: columnWidth }">{{ t('product::products.form.price_type') }}</th>
            <th style="width: 7%" />
          </tr>
        </thead>

        <!-- Render draggable as the tbody -->
        <draggable
          animation="180"
          chosen-class="drag-chosen"
          :disabled="!isMultipleOptions"
          drag-class="drag-dragging"
          ghost-class="drag-ghost"
          handle=".drag-handle"
          item-key="id"
          :list="option.values"
          tag="tbody"
        >
          <template #item="{ element: value, index }">
            <tr>
              <td v-if="isMultipleOptions" class="text-medium-emphasis" style="width:5%">
                <VIcon class="cursor-move drag-handle" icon="tabler-grip-vertical" />
              </td>

              <td v-if="isMultipleOptions">
                <VTextField
                  v-model="value.label[currentLanguage.id]"
                  density="comfortable"
                  :error="!!form.errors.value?.[`options.${optionIndex}.values.${index}.label.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`options.${optionIndex}.values.${index}.label.${currentLanguage.id}`]"
                  hide-details="auto"
                  :placeholder="t('product::attributes.products.options.*.values.*.label') + ` (${currentLanguage.name})`"
                />
              </td>

              <td>
                <VTextField
                  v-model="value.price"
                  :error="!!form.errors.value?.[`options.${optionIndex}.values.${index}.price`]"
                  :error-messages="form.errors.value?.[`options.${optionIndex}.values.${index}.price`]"
                  :placeholder="t('product::attributes.products.options.*.values.*.price')"
                  step="0.0001"
                  type="number"
                >
                  <template v-if="value.price_type=='fixed'" #prepend-inner>
                    {{ currency }}
                  </template>
                </VTextField>
              </td>

              <td>
                <VSelect
                  v-model="value.price_type"
                  density="comfortable"
                  :error="!!form.errors.value?.[`options.${optionIndex}.values.${index}.price_type`]"
                  :error-messages="form.errors.value?.[`options.${optionIndex}.values.${index}.price_type`]"
                  hide-details="auto"
                  item-title="name"
                  item-value="id"
                  :items="meta.priceTypes"
                  :placeholder="t('product::attributes.products.options.*.values.*.price_type')"
                />
              </td>

              <td>
                <div class="d-flex justify-space-between">
                  <VBtn
                    v-if="isMultipleOptions"
                    color="error"
                    :disabled="option.values.length === 1"
                    icon
                    size="small"
                    variant="text"
                    @click="removeRow(index)"
                  >
                    <VIcon icon="tabler-trash" />
                  </VBtn>
                  <VTooltip :text=" t('product::products.form.manage_ingredients')">
                    <template #activator="{ props }">
                      <VBtn
                        color="secondary"
                        icon
                        size="small"
                        v-bind="props"
                        variant="text"
                        @click="manageIngredients(index,value)"
                      >
                        <VIcon icon="tabler-salad" />
                      </VBtn>
                    </template>
                  </VTooltip>
                </div>
              </td>
            </tr>
          </template>
        </draggable>
      </table>
    </VCol>

    <VCol v-if="isMultipleOptions" cols="12">
      <VBtn color="secondary" @click="addRow">
        <VIcon icon="tabler-adjustments-plus" start />
        {{ t('product::products.form.add_row') }}
      </VBtn>
    </VCol>
  </VRow>
  <OptionManageIngredientsModal
    v-if="openManageIngredientsModal && optionValue"
    v-model="openManageIngredientsModal"
    :form="form"
    :index="optionValueIndex"
    :meta="meta"
    :option-index="optionIndex"
    :value="optionValue"
  />
</template>
