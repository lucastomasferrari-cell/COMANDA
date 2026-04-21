<script lang="ts" setup>
import { onBeforeMount, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { type RouteLocationRaw, useRouter } from 'vue-router'
import { useForm } from '@/modules/core/composables/form.ts'
import { useAppStore } from '@/modules/core/stores/appStore.ts'
import { useLoyaltyTier } from '@/modules/loyalty/composables/loyaltyTier.ts'
import SinglePicker from '@/modules/media/components/SinglePicker.vue'

const props = defineProps<{
  item?: Record<string, any> | null
  action: 'update' | 'create'
}>()

const { t } = useI18n()

const { update, store, getFormMeta } = useLoyaltyTier()
const router = useRouter()
const appStore = useAppStore()
const form = useForm({
  name: props.item?.name || {},
  benefits: props.item?.benefits || {},
  loyalty_program_id: props.item?.loyalty_program?.id,
  min_spend: props.item?.min_spend?.amount,
  multiplier: props.item?.multiplier || 1,
  order: props.item?.order,
  is_active: props.item?.is_active || false,
  files: {
    icon: props.item?.icon?.id || null,
  },
})

const meta = ref({ loyaltyPrograms: [] })

async function submit() {
  if (
    !form.loading.value
    && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
  ) {
    await router.push({ name: 'admin.loyalty_tiers.index' } as unknown as RouteLocationRaw)
  }
}

onBeforeMount(async () => {
  try {
    const response = (await getFormMeta()).data.body
    meta.value.loyaltyPrograms = response.loyalty_programs
  } catch {
    /* Empty */
  }
})
</script>

<template>
  <BaseForm v-slot="{ currentLanguage }" :action="action" has-multiple-language :loading="form.loading.value"
    resource="loyalty_tiers" @submit="submit">
    <VRow>
      <VCol cols="12" md="7">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('loyalty::loyalty_tiers.form.cards.loyalty_tier_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12">
                <VCol cols="12">
                  <VTextField v-model="form.state.name[currentLanguage.id]"
                    :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                    :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                    :label="t('loyalty::attributes.loyalty_tiers.name') + ` ( ${currentLanguage.name} )`" />
                </VCol>
                <VCol cols="12">
                  <VTextarea v-model="form.state.benefits[currentLanguage.id]" auto-grow clearable
                    :error="!!form.errors.value?.[`benefits.${currentLanguage.id}`]"
                    :error-messages="form.errors.value?.[`benefits.${currentLanguage.id}`]"
                    :label="t('loyalty::attributes.loyalty_tiers.benefits') + ` ( ${currentLanguage.name} )`"
                    rows="6" />
                </VCol>
                <VCol cols="12">
                  <VCheckbox v-model="form.state.is_active" :label="t('loyalty::attributes.loyalty_tiers.is_active')" />
                </VCol>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="5">
        <VRow>
          <VCol cols="12">
            <VCard>
              <VCardTitle class="d-flex justify-space-between align-center mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-medal" size="20" />
                  <span>{{ t('loyalty::loyalty_tiers.form.cards.program_and_rules') }}</span>
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>

                  <VCol cols="12" md="6">
                    <VSelect v-model="form.state.loyalty_program_id" :error="!!form.errors.value?.loyalty_program_id"
                      :error-messages="form.errors.value?.loyalty_program_id" item-title="name" item-value="id"
                      :items="meta.loyaltyPrograms"
                      :label="t('loyalty::attributes.loyalty_tiers.loyalty_program_id')" />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField v-model="form.state.min_spend" :error="!!form.errors.value?.min_spend"
                      :error-messages="form.errors.value?.min_spend"
                      :label="t('loyalty::attributes.loyalty_tiers.min_spend')" :prefix="appStore.currency" />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField v-model="form.state.multiplier" :error="!!form.errors.value?.multiplier"
                      :error-messages="form.errors.value?.multiplier"
                      :label="t('loyalty::attributes.loyalty_tiers.multiplier')" />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField v-model="form.state.order" :error="!!form.errors.value?.order"
                      :error-messages="form.errors.value?.order"
                      :label="t('loyalty::attributes.loyalty_tiers.order')" />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12">
            <VCard>
              <VCardTitle class="d-flex justify-space-between align-center mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-library-photo" size="20" />
                  <span>{{ t('loyalty::loyalty_tiers.form.cards.media') }}</span>
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12">
                    <SinglePicker v-model="form.state.files.icon" :label="t('loyalty::attributes.loyalty_tiers.icon')"
                      :media="item?.icon" mime="image" />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCol>
    </VRow>

  </BaseForm>
</template>
