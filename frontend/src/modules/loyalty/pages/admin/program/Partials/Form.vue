<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useLoyaltyProgram } from '@/modules/loyalty/composables/loyaltyProgram.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()

  const { update, store } = useLoyaltyProgram()
  const router = useRouter()
  const appStore = useAppStore()
  const form = useForm({
    name: props.item?.name || {},
    earning_rate: props.item?.earning_rate?.amount,
    redemption_rate: props.item?.redemption_rate?.amount || 0.001,
    min_redeem_points: props.item?.min_redeem_points || 1,
    points_expire_after: props.item?.points_expire_after,
    is_active: props.item?.is_active || false,
  })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.loyalty_programs.index' } as unknown as RouteLocationRaw)
    }
  }

</script>

<template>
  <BaseForm
    v-slot="{ currentLanguage }"
    :action="action"
    has-multiple-language
    :loading="form.loading.value"
    resource="loyalty_programs"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('loyalty::loyalty_programs.form.cards.loyalty_program_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('loyalty::attributes.loyalty_programs.name') + ` ( ${currentLanguage.name} )`"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.earning_rate"
                  :error="!!form.errors.value?.earning_rate"
                  :error-messages="form.errors.value?.earning_rate"
                  :label="t('loyalty::attributes.loyalty_programs.earning_rate')"
                  :prefix="appStore.currency"
                />
              </VCol>
              <!--              <VCol cols="12" md="6">-->
              <!--                <VTextField-->
              <!--                  v-model="form.state.redemption_rate"-->
              <!--                  :error="!!form.errors.value?.redemption_rate"-->
              <!--                  :error-messages="form.errors.value?.redemption_rate"-->
              <!--                  :label="t('loyalty::attributes.loyalty_programs.redemption_rate')"-->
              <!--                  :prefix="appStore.currency"-->
              <!--                />-->
              <!--              </VCol>-->
              <!--              <VCol cols="12" md="6">-->
              <!--                <VTextField-->
              <!--                  v-model="form.state.min_redeem_points"-->
              <!--                  :error="!!form.errors.value?.min_redeem_points"-->
              <!--                  :error-messages="form.errors.value?.min_redeem_points"-->
              <!--                  :label="t('loyalty::attributes.loyalty_programs.min_redeem_points')"-->
              <!--                />-->
              <!--              </VCol>-->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.points_expire_after"
                  :error="!!form.errors.value?.points_expire_after"
                  :error-messages="form.errors.value?.points_expire_after"
                  :label="t('loyalty::attributes.loyalty_programs.points_expire_after')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('loyalty::attributes.loyalty_programs.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

    </VRow>
  </BaseForm>
</template>
