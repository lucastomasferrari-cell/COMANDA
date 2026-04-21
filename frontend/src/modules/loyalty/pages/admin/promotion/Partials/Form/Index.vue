<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useLoyaltyPromotion } from '@/modules/loyalty/composables/loyaltyPromotion.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import AvailabilitySchedule from './Cards/AvailabilitySchedule.vue'
  import Conditions from './Cards/Conditions.vue'
  import LoyaltyPromotionInformation from './Cards/LoyaltyPromotionInformation.vue'
  import RewardValueAndPoints from './Cards/RewardValueAndPoints.vue'
  import UsageRules from './Cards/UsageRules.vue'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { update, store, getFormMeta } = useLoyaltyPromotion()
  const router = useRouter()
  const form = useForm({
    name: props.item?.name || {},
    description: props.item?.description || {},
    loyalty_program_id: props.item?.loyalty_program?.id,
    type: props.item?.type?.id,
    usage_limit: props.item?.usage_limit,
    per_customer_limit: props.item?.per_customer_limit,
    bonus_points: props.item?.bonus_points,
    multiplier: props.item?.multiplier,
    starts_at: props.item?.starts_at,
    ends_at: props.item?.ends_at,
    conditions: {
      min_spend: props.item?.conditions?.min_spend,
      branch_ids: props.item?.conditions?.branch_ids,
      categories: props.item?.conditions?.categories,
      available_days: props.item?.conditions?.available_days,
      valid_days: props.item?.conditions?.valid_days,
    },
    meta: {},
    is_active: props.item?.is_active || false,
  })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.loyalty_promotions.index' } as unknown as RouteLocationRaw)
    }
  }

  const meta = ref({
    branches: [],
    categories: [],
    types: [],
    programs: [],
    days: [],
  })

  onBeforeMount(() => {
    loadFormData()
  })

  const loadFormData = async () => {
    try {
      const response = (await getFormMeta()).data.body
      meta.value.types = response.types || meta.value.types
      meta.value.programs = response.loyalty_programs || meta.value.programs
      meta.value.days = response.days || meta.value.days
      meta.value.categories = response.categories || meta.value.categories
      meta.value.branches = response.branches || meta.value.branches
    } catch {
    /* Empty */
    }
  }

  watch(
    () => form.state.type,
    () => {
      form.state.conditions.categories = null
      form.state.multiplier = null
      form.state.bonus_points = null
    })
</script>

<template>
  <BaseForm
    v-slot="{ currentLanguage }"
    :action="action"
    has-multiple-language
    :loading="form.loading.value"
    resource="loyalty_promotions"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="7">
        <VRow>
          <LoyaltyPromotionInformation :current-language="currentLanguage" :form="form" :meta="meta" />
          <Conditions :form="form" :meta="meta" />
        </VRow>
      </VCol>
      <VCol cols="12" md="5">
        <VRow>
          <RewardValueAndPoints :form="form" :meta="meta" />
          <UsageRules :form="form" :meta="meta" />
          <AvailabilitySchedule :form="form" />
        </VRow>
      </VCol>
    </VRow>
  </BaseForm>
</template>
