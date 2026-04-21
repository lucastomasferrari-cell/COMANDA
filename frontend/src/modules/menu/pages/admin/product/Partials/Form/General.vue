<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{ form: any, currentLanguage: Record<string, any>, meta: Record<string, any> }>()
  const { t } = useI18n()
</script>

<template>
  <VCard>
    <VCardTitle class="d-flex justify-space-between align-center mb-2">
      <div class="d-flex align-center">
        <VIcon class="me-2" icon="tabler-info-circle" size="20" />
        <span>{{ t('product::products.form.cards.general_information') }}</span>
      </div>
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12">
          <VTextField
            v-model="form.state.name[currentLanguage.id]"
            :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
            :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
            :label="t('product::attributes.products.name') + ` ( ${currentLanguage.name} )`"
          />
        </VCol>
        <VCol cols="12">
          <VTextField
            v-model="form.state.sku"
            clearable
            :error="!!form.errors.value?.sku"
            :error-messages="form.errors.value?.sku"
            :label="t('product::attributes.products.sku')"
          />
        </VCol>
        <VCol cols="12">
          <VTextarea
            v-model="form.state.description[currentLanguage.id]"
            auto-grow
            clearable
            :error="!!form.errors.value?.[`description.${currentLanguage.id}`]"
            :error-messages="form.errors.value?.[`description.${currentLanguage.id}`]"
            :label="t('product::attributes.products.description') + ` ( ${currentLanguage.name} )`"
            rows="8"
          />
        </VCol>
        <VCol cols="12" md="6">
          <VSelect
            v-model="form.state.categories"
            chips
            clearable
            :error="!!form.errors.value?.categories"
            :error-messages="form.errors.value?.categories"
            item-title="name"
            item-value="id"
            :items="meta.categories"
            :label="t('product::attributes.products.categories')"
            multiple
          />
        </VCol>
        <VCol cols="12" md="6">
          <VSelect
            v-model="form.state.taxes"
            chips
            clearable
            :error="!!form.errors.value?.taxes"
            :error-messages="form.errors.value?.taxes"
            item-title="name"
            item-value="id"
            :items="meta.taxes"
            :label="t('product::attributes.products.taxes')"
            multiple
          />
        </VCol>
        <VCol cols="12">
          <VCheckbox
            v-model="form.state.is_active"
            :label="t('product::attributes.products.is_active')"
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
