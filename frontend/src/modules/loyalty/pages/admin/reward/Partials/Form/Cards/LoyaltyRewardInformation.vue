<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    form: Record<string, any>
    meta: Record<string, any>
    currentLanguage: Record<string, any>
  }>()

  const { t } = useI18n()
</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-info-circle" size="20" />
          <span>{{ t('loyalty::loyalty_rewards.form.cards.loyalty_reward_information') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" md="8">
            <VTextField
              v-model="form.state.name[currentLanguage.id]"
              :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
              :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
              :label="t('loyalty::attributes.loyalty_rewards.name') + ` ( ${currentLanguage.name} )`"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VSelect
              v-model="form.state.type"
              :error="!!form.errors.value?.type"
              :error-messages="form.errors.value?.type"
              item-title="name"
              item-value="id"
              :items="meta.types"
              :label="t('loyalty::attributes.loyalty_rewards.type')"
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="form.state.description[currentLanguage.id]"
              auto-grow
              clearable
              :error="!!form.errors.value?.[`description.${currentLanguage.id}`]"
              :error-messages="form.errors.value?.[`description.${currentLanguage.id}`]"
              :label="t('loyalty::attributes.loyalty_rewards.description') + ` ( ${currentLanguage.name} )`"
              rows="4"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.loyalty_program_id"
              :error="!!form.errors.value?.loyalty_program_id"
              :error-messages="form.errors.value?.loyalty_program_id"
              item-title="name"
              item-value="id"
              :items="meta.programs"
              :label="t('loyalty::attributes.loyalty_rewards.loyalty_program_id')"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.loyalty_tier_id"
              clearable
              :error="!!form.errors.value?.loyalty_tier_id"
              :error-messages="form.errors.value?.loyalty_tier_id"
              item-title="name"
              item-value="id"
              :items="meta.tiers"
              :label="t('loyalty::attributes.loyalty_rewards.loyalty_tier_id')"
            />
          </VCol>
          <VCol cols="12">
            <VCheckbox
              v-model="form.state.is_active"
              :label="t('loyalty::attributes.loyalty_rewards.is_active')"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
