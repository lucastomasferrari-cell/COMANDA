<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { generateVoucherCode } from '@/modules/core/utils/support.ts'

  const props = defineProps<{
    form: any
    meta: Record<string, any>
    currentLanguage: Record<string, any>
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { form } = toRefs(props)

  onMounted(() => {
    if (props.action === 'create') {
      refreshCode()
    }
  })

  const refreshCode = () => {
    form.value.state.code = generateVoucherCode()
  }
</script>

<template>
  <VCol cols="12">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-info-circle" size="20" />
          <span>{{ t('voucher::vouchers.form.cards.voucher_information') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.name[currentLanguage.id]"
              :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
              :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
              :label="t('voucher::attributes.vouchers.name') + ` ( ${currentLanguage.name} )`"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.state.code"
              append-inner-icon="tabler-refresh"
              class="voucher-code-field"
              :error="!!form.errors.value?.code"
              :error-messages="form.errors.value?.code"
              :label="t('voucher::attributes.vouchers.code')"
              @click:append-inner="refreshCode"
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
              :label="t('voucher::attributes.vouchers.branch_id')"
            />
          </VCol>

          <VCol cols="12">
            <VTextarea
              v-model="form.state.description[currentLanguage.id]"
              auto-grow
              clearable
              :error="!!form.errors.value?.[`description.${currentLanguage.id}`]"
              :error-messages="form.errors.value?.[`description.${currentLanguage.id}`]"
              :label="t('voucher::attributes.vouchers.description') + ` ( ${currentLanguage.name} )`"
              rows="4"
            />
          </VCol>
          <VCol cols="12">
            <VRow>
              <VCol cols="6">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('voucher::attributes.vouchers.is_active')"
                />
              </VCol>
            </VRow>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>
