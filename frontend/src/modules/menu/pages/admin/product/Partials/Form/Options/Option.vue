<script lang="ts" setup>
  import { computed, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import OptionValues from './OptionValues.vue'

  const props = defineProps<{
    option: any
    form: any
    currentLanguage: Record<string, any>
    meta: Record<string, any>
    index: number
  }>()
  const { t } = useI18n()
  const removeOption = () => {
    if (props.form.state.options.length == 1) {
      props.form.state.options = []
      props.form.state.options.push({
        name: {},
        type: null,
        is_required: false,
        is_global: false,
        values: [],
      })
    } else {
      props.form.state.options.splice(props.index, 1)
    }
  }

  const isMultipleOptions = computed(() => hasMultipleOptions(props.option.type))

  const hasMultipleOptions = (type: string) => ['select', 'multiple_select', 'checkbox', 'radio'].includes(type)

  watch(() => props.option.type, (newValue, oldValue) => {
    if (!oldValue || hasMultipleOptions(newValue) != hasMultipleOptions(oldValue)) {
      props.option.values = []
      if (isMultipleOptions.value) {
        props.option.values.push({
          label: {},
          price: null,
          price_type: 'fixed',
          ingredients: [],
        })
      } else {
        props.option.values.push({
          price: null,
          price_type: 'fixed',
          ingredients: [],
        })
      }
    }
  })
</script>

<template>
  <VExpansionPanel
    class="border rounded"
  >
    <VExpansionPanelTitle class="bg-brown-darken-2">
      <div class="d-flex align-center justify-space-between w-100">
        <div class="d-flex align-center">
          <VIcon
            class="cursor-move drag-handle me-2"
            icon="tabler-grip-vertical"
            size="20"
          />
          <span class="font-weight-bold">
            {{ option.name[currentLanguage.id] || t('product::products.form.new_option') }}
          </span>
        </div>
        <VBtn
          color="error"
          icon
          size="small"
          variant="text"
          @click.stop="removeOption"
        >
          <VIcon icon="tabler-trash" />
        </VBtn>
      </div>
    </VExpansionPanelTitle>
    <VExpansionPanelText>
      <VRow>
        <VCol cols="12" md="5">
          <VTextField
            v-model="option.name[currentLanguage.id]"
            :error="!!form.errors.value?.[`options.${index}.name.${currentLanguage.id}`]"
            :error-messages="form.errors.value?.[`options.${index}.name.${currentLanguage.id}`]"
            :label="t('product::attributes.products.options.*.name') + ` (${currentLanguage.name})`"
          />
        </VCol>
        <VCol cols="12" md="4">
          <VSelect
            v-model="option.type"
            :error="!!form.errors.value?.[`options.${index}.type`]"
            :error-messages="form.errors.value?.[`options.${index}.type`]"
            item-title="name"
            item-value="id"
            :items="meta.optionTypes"
            :label="t('product::attributes.products.options.*.type')"
          />
        </VCol>
        <VCol cols="12" md="3">
          <VCheckbox
            v-model="option.is_required"
            :label="t('product::attributes.products.options.*.is_required')"
          />
        </VCol>
      </VRow>
      <VRow>
        <VCol cols="12">
          <OptionValues
            :currency="meta.currency"
            :current-language="currentLanguage"
            :form="form"
            :is-multiple-options="isMultipleOptions"
            :meta="meta"
            :option="option"
            :option-index="index"
          />
        </VCol>
      </VRow>
    </VExpansionPanelText>
  </VExpansionPanel>
</template>

<style lang="scss" scoped>
:deep(.v-expansion-panel-title) {
  padding: 7px 20px !important;
  background-color: rgba(var(--v-theme-on-background), 0.03) !important;
}

:deep(.v-expansion-panel-text__wrapper) {
  padding: 20px !important;
}
</style>
