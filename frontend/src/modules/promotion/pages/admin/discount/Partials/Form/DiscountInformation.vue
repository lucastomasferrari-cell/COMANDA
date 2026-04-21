<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    form: any
    meta: Record<string, any>
    currentLanguage: Record<string, any>
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { form } = toRefs(props)
</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-info-circle" size="20" />
          <span>{{ t('discount::discounts.form.cards.discount_information') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.name[currentLanguage.id]"
              :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
              :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
              :label="t('discount::attributes.discounts.name') + ` ( ${currentLanguage.name} )`"
            />
          </VCol>
          <VCol v-if="action=='create' && !user?.assigned_to_branch" cols="12" md="6">
            <VSelect
              v-model="form.state.branch_id"
              :error="!!form.errors.value?.branch_id"
              :error-messages="form.errors.value?.branch_id"
              item-title="name"
              item-value="id"
              :items="meta.branches"
              :label="t('discount::attributes.discounts.branch_id')"
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="form.state.description[currentLanguage.id]"
              auto-grow
              clearable
              :error="!!form.errors.value?.[`description.${currentLanguage.id}`]"
              :error-messages="form.errors.value?.[`description.${currentLanguage.id}`]"
              :label="t('discount::attributes.discounts.description') + ` ( ${currentLanguage.name} )`"
              rows="4"
            />
          </VCol>
          <VCol cols="12">
            <VRow>
              <VCol cols="6">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('discount::attributes.discounts.is_active')"
                />
              </VCol>
            </VRow>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
