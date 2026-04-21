<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  defineProps<{
    form: Record<string, any>
    meta: Record<string, any>
  }>()

  const { t } = useI18n()
  const { currency } = useAppStore()

</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-coin" size="20" />
          <span>{{ t('loyalty::loyalty_rewards.form.cards.reward_and_value_points') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="form.state.points_cost"
              v-integer-en
              :error="!!form.errors.value?.points_cost"
              :error-messages="form.errors.value?.points_cost"
              :label="t('loyalty::attributes.loyalty_rewards.points_cost')"
              suffix="Pts"
            />
          </VCol>
          <template v-if="['discount','cashback','gift_card','voucher_code'].includes(form.state.type)">
            <VCol cols="12" md="4">
              <VSelect
                v-model="form.state.value_type"
                :error="!!form.errors.value?.value_type"
                :error-messages="form.errors.value?.value_type"
                item-title="name"
                item-value="id"
                :items="meta.priceTypes"
                :label="t('loyalty::attributes.loyalty_rewards.value_type')"
              />
            </VCol>
            <VCol cols="12" md="8">
              <VTextField
                v-model="form.state.value"
                v-decimal-en
                :error="!!form.errors.value?.value"
                :error-messages="form.errors.value?.value"
                :label="t('loyalty::attributes.loyalty_rewards.value')"
                :prefix="form.state.value_type=='fixed'? currency:''"
                :suffix="form.state.value_type=='percent'?'%':''"
              />
            </VCol>
          </template>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
