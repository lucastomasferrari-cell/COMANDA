<script lang="ts" setup>
import { useI18n } from 'vue-i18n'
import SinglePicker from '@/modules/media/components/SinglePicker.vue'

defineProps<{
  form: Record<string, any>
  item?: Record<string, any> | null
}>()
const { t } = useI18n()
</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-library-photo" size="20" />
          <span>{{ t('loyalty::loyalty_rewards.form.cards.media') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <SinglePicker v-model="form.state.files.icon" :label="t('loyalty::attributes.loyalty_rewards.files.icon')"
              :media="item?.icon" mime="image" />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
