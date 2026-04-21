<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    form: Record<string, any>
    meta: Record<string, any>
  }>()

  const { t } = useI18n()
</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-filter" size="20" />
          <span>{{ t('loyalty::loyalty_promotions.form.cards.conditions') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol
            v-if="form.state.type=='new_member'"
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.state.conditions.valid_days"
              v-integer-en
              :error="!!form.errors.value?.['conditions.valid_days']"
              :error-messages="form.errors.value?.['conditions.valid_days']"
              :label="t('loyalty::attributes.loyalty_promotions.conditions.valid_days')"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.conditions.min_spend"
              v-decimal-en
              clearable
              :error="!!form.errors.value?.['conditions.min_spend']"
              :error-messages="form.errors.value?.['conditions.min_spend']"
              :label="t('loyalty::attributes.loyalty_promotions.conditions.min_spend')"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.conditions.branch_ids"
              chips
              clearable
              :error="!!form.errors.value?.['conditions.branch_ids']"
              :error-messages="form.errors.value?.['conditions.branch_ids']"
              item-title="name"
              item-value="id"
              :items="meta.branches"
              :label="t('loyalty::attributes.loyalty_promotions.conditions.branch_ids')"
              multiple
            />
          </VCol>
          <VCol v-if="form.state.type==='category_boost'" cols="12" md="6">
            <VSelect
              v-model="form.state.conditions.categories"
              chips
              clearable
              :error="!!form.errors.value?.['conditions.categories']"
              :error-messages="form.errors.value?.['conditions.categories']"
              item-title="name"
              item-value="slug"
              :items="meta.categories"
              :label="t('loyalty::attributes.loyalty_promotions.conditions.categories')"
              multiple
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.conditions.available_days"
              chips
              clearable
              :error="!!form.errors.value?.['conditions.available_days']"
              :error-messages="form.errors.value?.['conditions.available_days']"
              item-title="name"
              item-value="id"
              :items="meta.days"
              :label="t('loyalty::attributes.loyalty_promotions.conditions.available_days')"
              multiple
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
