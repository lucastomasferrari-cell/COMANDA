<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import draggable from 'vuedraggable'

  const props = defineProps<{
    form: any
    meta: Record<string, any>
    currentLanguage: Record<string, any>
    isMultipleOptions: boolean
    currency: string
  }>()

  const { t } = useI18n()

  // Ensure every value has a stable id
  const ensureIds = () => {
    for (const v of props.form.state.values) {
      if (v.id == null) v.id = crypto.randomUUID?.() || `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`
      if (!v.label) v.label = {}
      if (v.price_type == null) v.price_type = 'fixed'
    }
  }
  ensureIds()

  const addRow = () => {
    props.form.state.values.push({
      id: crypto.randomUUID?.() || `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
      label: {},
      price: null,
      price_type: 'fixed',
    })
  }

  const removeRow = (index: number) => {
    props.form.state.values.splice(index, 1)
  }

  const columnWidth = computed(() => (props.isMultipleOptions ? '20%' : '50%'))

</script>

<template>
  <VCard>
    <VCardTitle class="d-flex justify-space-between align-center mb-2">
      <div class="d-flex align-center">
        <VIcon class="me-2" icon="tabler-adjustments" size="20" />
        <span>{{ t('option::options.form.cards.values') }}</span>
      </div>
    </VCardTitle>

    <VCardText>
      <VRow>
        <VCol v-if="!form.state.type" cols="12">
          <VAlert type="info" variant="tonal">
            <template #prepend>
              <VIcon icon="tabler-info-circle" />
            </template>
            {{ t('option::options.form.please_select_a_option_type') }}
          </VAlert>
        </VCol>

        <VCol v-else cols="12">
          <table class="text-center bordered-table">
            <thead>
              <tr>
                <th v-if="isMultipleOptions" style="width:5%" />
                <th v-if="isMultipleOptions" :style="{ width: columnWidth }">{{ t('option::options.form.label') }}</th>
                <th :style="{ width: columnWidth }">{{ t('option::options.form.price') }}</th>
                <th :style="{ width: columnWidth }">{{ t('option::options.form.price_type') }}</th>
                <th v-if="isMultipleOptions" style="width:5%" />
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
              :list="form.state.values"
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
                      :error="!!form.errors.value?.[`values.${index}.label.${currentLanguage.id}`]"
                      :error-messages="form.errors.value?.[`values.${index}.label.${currentLanguage.id}`]"
                      hide-details="auto"
                      :placeholder="t('option::attributes.options.values.*.label') + ` (${currentLanguage.name})`"
                    />
                  </td>

                  <td>
                    <VTextField
                      v-model="value.price"
                      density="comfortable"
                      :error="!!form.errors.value?.[`values.${index}.price`]"
                      :error-messages="form.errors.value?.[`values.${index}.price`]"
                      hide-details="auto"
                      :placeholder="t('option::attributes.options.values.*.price')"
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
                      :error="!!form.errors.value?.[`values.${index}.price_type`]"
                      :error-messages="form.errors.value?.[`values.${index}.price_type`]"
                      hide-details="auto"
                      item-title="name"
                      item-value="id"
                      :items="meta.priceTypes"
                      :placeholder="t('option::attributes.options.values.*.price_type')"
                    />
                  </td>

                  <td v-if="isMultipleOptions">
                    <VBtn
                      color="error"
                      :disabled="form.state.values.length === 1"
                      icon
                      size="small"
                      variant="text"
                      @click="removeRow(index)"
                    >
                      <VIcon icon="tabler-trash" />
                    </VBtn>
                  </td>
                </tr>
              </template>
            </draggable>
          </table>
        </VCol>

        <VCol v-if="isMultipleOptions" cols="12">
          <VBtn color="secondary" @click="addRow">
            <VIcon icon="tabler-adjustments-plus" start />
            {{ t('option::options.form.add_row') }}
          </VBtn>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
