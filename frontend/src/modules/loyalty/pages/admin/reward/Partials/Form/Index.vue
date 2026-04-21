<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useLoyaltyReward } from '@/modules/loyalty/composables/loyaltyReward.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import AvailabilitySchedule from './Cards/AvailabilitySchedule.vue'
  import Conditions from './Cards/Conditions.vue'
  import Configuration from './Cards/Configuration/Index.vue'
  import LoyaltyRewardInformation from './Cards/LoyaltyRewardInformation.vue'
  import Media from './Cards/Media.vue'
  import RewardValueAndPoints from './Cards/RewardValueAndPoints.vue'
  import UsageAndRedemptionRules from './Cards/UsageAndRedemptionRules.vue'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { update, store, getFormMeta } = useLoyaltyReward()
  const router = useRouter()
  const form = useForm({
    name: props.item?.name || {},
    description: props.item?.description || {},
    loyalty_program_id: props.item?.loyalty_program?.id,
    loyalty_tier_id: props.item?.loyalty_tier?.id,
    type: props.item?.type?.id,
    points_cost: props.item?.points_cost,
    value: props.item?.value?.amount || props.item?.value,
    value_type: props.item?.value_type?.id,
    max_redemptions_per_order: props.item?.max_redemptions_per_order,
    usage_limit: props.item?.usage_limit,
    per_customer_limit: props.item?.per_customer_limit,
    starts_at: props.item?.starts_at,
    ends_at: props.item?.ends_at,
    conditions: {
      min_spend: props.item?.conditions?.min_spend,
      branch_ids: props.item?.conditions?.branch_ids,
      available_days: props.item?.conditions?.available_days,
    },
    meta: {
      min_order_total: props.item?.meta?.min_order_total,
      max_order_total: props.item?.meta?.max_order_total,
      max_discount: props.item?.meta?.max_discount,
      expires_in_days: props.item?.meta?.expires_in_days,
      usage_limit: props.item?.meta?.usage_limit || 1,
      code_prefix: props.item?.meta?.code_prefix,
      product_sku: props.item?.meta?.product_sku,
      quantity: props.item?.meta?.quantity || 1,
      target_tier: props.item?.meta?.target_tier,
    },
    files: {
      icon: props.item?.icon?.id || null,
    },
    is_active: props.item?.is_active || false,
  })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.loyalty_rewards.index' } as unknown as RouteLocationRaw)
    }
  }

  const meta = ref({
    branches: [],
    types: [],
    programs: [],
    priceTypes: [],
    days: [],
    products: [],
    tiers: [],
  })

  onBeforeMount(() => {
    loadFormData()
    if (form.state.loyalty_program_id) {
      loadFormData(form.state.loyalty_program_id)
    }
  })

  watch(() => form.state.loyalty_program_id,
        newValue => {
          form.state.loyalty_tier_id = null
          meta.value.tiers = []
          loadFormData(newValue)
        })

  const loadFormData = async (programId?: number) => {
    try {
      const response = (await getFormMeta(programId)).data.body
      meta.value.types = response.types || meta.value.types
      meta.value.programs = response.loyalty_programs || meta.value.programs
      meta.value.priceTypes = response.price_types || meta.value.priceTypes
      meta.value.days = response.days || meta.value.days
      meta.value.products = response.products || meta.value.products
      meta.value.branches = response.branches || meta.value.branches
      if (programId) {
        meta.value.tiers = response.loyalty_tiers
      }
    } catch {
    /* Empty */
    }
  }
</script>

<template>
  <BaseForm
    v-slot="{ currentLanguage }"
    :action="action"
    has-multiple-language
    :loading="form.loading.value"
    resource="loyalty_rewards"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="7">
        <VRow>
          <LoyaltyRewardInformation :current-language="currentLanguage" :form="form" :meta="meta" />
          <Configuration :form="form" :meta="meta" />
          <Conditions :form="form" :meta="meta" />
        </VRow>
      </VCol>
      <VCol cols="12" md="5">
        <VRow>
          <Media :form="form" :item="item" />
          <RewardValueAndPoints :form="form" :meta="meta" />
          <UsageAndRedemptionRules :form="form" :meta="meta" />
          <AvailabilitySchedule :form="form" />
        </VRow>
      </VCol>
    </VRow>
  </BaseForm>
</template>
