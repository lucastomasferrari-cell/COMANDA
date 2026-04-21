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
          <VIcon class="me-2" icon="tabler-coin" size="20" />
          <span>{{ t('loyalty::loyalty_promotions.form.cards.multiplier_and_bonus_points') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow v-if="form.state.type">
          <VCol v-if="['bonus_points','new_member'].includes(form.state.type)" cols="12">
            <VTextField
              v-model="form.state.bonus_points"
              v-integer-en
              :error="!!form.errors.value?.bonus_points"
              :error-messages="form.errors.value?.bonus_points"
              :label="t('loyalty::attributes.loyalty_promotions.bonus_points')"
              suffix="Pts"
            />
          </VCol>
          <template v-else>
            <VCol cols="12">
              <VTextField
                v-model="form.state.multiplier"
                v-decimal-en
                :error="!!form.errors.value?.multiplier"
                :error-messages="form.errors.value?.multiplier"
                :label="t('loyalty::attributes.loyalty_promotions.multiplier')"
              />
            </VCol>
          </template>
        </VRow>
        <VAlert v-else color="info">
          {{ t('loyalty::loyalty_promotions.form.alert_note_for_multiplier_and_bonus_points') }}
        </VAlert>
      </VCardText>
    </VCard>
  </VCol>
</template>
