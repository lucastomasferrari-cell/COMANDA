<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import Discount from './Discount.vue'
  import FreeItem from './FreeItem.vue'
  import TierUpgrade from './TierUpgrade.vue'

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
          <VIcon class="me-2" icon="tabler-settings-cog" size="20" />
          <span>{{ t('loyalty::loyalty_rewards.form.cards.configuration') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow v-if="form.state.type">
          <Discount v-if="['discount','voucher_code'].includes(form.state.type)" :form="form" :meta="meta" />
          <FreeItem v-if="form.state.type==='free_item'" :form="form" :meta="meta" />
          <TierUpgrade v-if="form.state.type==='tier_upgrade'" :form="form" :meta="meta" />
        </VRow>
        <VAlert v-else color="info">
          {{ t('loyalty::loyalty_rewards.form.alert_note_for_configuration') }}
        </VAlert>
      </VCardText>
    </VCard>
  </VCol>
</template>
