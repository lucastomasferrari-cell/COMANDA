<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    form: any
    meta: Record<string, any>
    currentLanguage: Record<string, any>
  }>()
  const { t } = useI18n()

</script>

<template>
  <VCard>
    <VCardTitle class="d-flex justify-space-between align-center mb-2">
      <div class="d-flex align-center">
        <VIcon class="me-2" icon="tabler-info-circle" size="20" />
        <span>{{ t('option::options.form.cards.option_information') }}</span>
      </div>
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12">
          <VTextField
            v-model="form.state.name[currentLanguage.id]"
            :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
            :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
            :label="t('option::attributes.options.name')"
          />
        </VCol>
        <VCol cols="12">
          <VTextField
            v-model="form.state.sku"
            clearable
            :error="!!form.errors.value?.sku"
            :error-messages="form.errors.value?.sku"
            :hint="t('option::attributes.options.sku_hint')"
            :label="t('option::attributes.options.sku')"
            persistent-hint
            :readonly="form.state.sku_locked"
          />
        </VCol>
        <VCol cols="12">
          <VSelect
            v-model="form.state.type"
            :error="!!form.errors.value?.type"
            :error-messages="form.errors.value?.type"
            item-title="name"
            item-value="id"
            :items="meta.types"
            :label="t('option::attributes.options.type')"
          />
        </VCol>
        <VCol cols="12">
          <VCheckbox
            v-model="form.state.is_required"
            :label="t('option::attributes.options.is_required')"
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
