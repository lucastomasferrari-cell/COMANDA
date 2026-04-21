<script lang="ts" setup>
  import { onBeforeMount, ref } from 'vue'
  import { type RouteLocationRaw, useRouter } from 'vue-router'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useVoucher } from '@/modules/promotion/composables/voucher.ts'
  import Availability from './Availability.vue'
  import Conditions from './Conditions.vue'
  import Details from './Details.vue'
  import Limits from './Limits.vue'
  import VoucherInformation from './VoucherInformation.vue'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { user } = useAuth()
  const { getFormMeta, update, store } = useVoucher()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name || {},
    description: props.item?.description || {},
    code: props.item?.code,
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    type: props.item?.type?.id,
    value: props.item?.value?.amount || props.item?.value,
    minimum_spend: props.item?.minimum_spend?.amount,
    maximum_spend: props.item?.maximum_spend?.amount,
    max_discount: props.item?.max_discount?.amount,
    usage_limit: props.item?.usage_limit,
    per_customer_limit: props.item?.per_customer_limit,
    start_date: props.item?.start_date,
    end_date: props.item?.end_date,
    conditions: {
      available_days: props.item?.conditions?.available_days,
      categories: props.item?.conditions?.categories,
      products: props.item?.conditions?.products,
      order_types: props.item?.conditions?.order_types,
    },
    is_active: props.item?.is_active || false,
  })

  const meta = ref({
    branches: [],
    categories: [],
    days: [],
    types: [],
    products: [],
    orderTypes: [],
  })

  async function submit () {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.vouchers.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(() => {
    loadFormData()
  })

  async function loadFormData () {
    try {
      const response = (await getFormMeta()).data.body
      meta.value.branches = response.branches || meta.value.branches
      meta.value.categories = response.categories || meta.value.categories
      meta.value.days = response.days || meta.value.days
      meta.value.orderTypes = response.orderTypes || meta.value.orderTypes
      meta.value.types = response.types || meta.value.types
      meta.value.products = response.products || meta.value.products
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
    resource="vouchers"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="6">
        <VRow>
          <VoucherInformation
            :action="action"
            :current-language="currentLanguage"
            :form="form"
            :meta="meta"
          />
          <Conditions :form="form" :meta="meta" />
        </VRow>
      </VCol>
      <VCol cols="12" md="6">
        <VRow>
          <Details :form="form" :meta="meta" />
          <Availability :form="form" :meta="meta" />
          <Limits :form="form" :meta="meta" />
        </VRow>
      </VCol>
    </VRow>
    <VRow />
  </BaseForm>
</template>
