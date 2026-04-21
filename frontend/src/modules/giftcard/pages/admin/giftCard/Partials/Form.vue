<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { onBeforeMount, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import BaseForm from '@/modules/core/components/Form/BaseForm.vue'
  import DateTimePicker from '@/modules/core/components/Form/DateTimePicker.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useGiftCard } from '@/modules/giftcard/composables/giftCard.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { getFormMeta, store, update } = useGiftCard()
  const router = useRouter()

  const form = useForm({
    code: props.item?.code || null,
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id || null,
    customer_id: props.item?.customer?.id || null,
    status: props.item?.status?.id || 'active',
    initial_balance: props.item?.initial_balance?.amount || null,
    currency: props.item?.currency || null,
    expiry_date: props.item?.expiry_date || null,
    notes: props.item?.notes || null,
  })

  type GiftCardFormMeta = {
    branches: Array<{ id: number, name: string, currency?: string | null }>
    customers: Array<{ id: number, name: string }>
    statuses: Array<{ id: string, name: string }>
    default_currency: string | null
  }

  const meta = ref<GiftCardFormMeta>({
    branches: [],
    customers: [],
    statuses: [],
    default_currency: null,
  })

  function syncCurrencyFromBranch () {
    const selectedBranch = meta.value.branches.find((branch: Record<string, any>) => branch.id === form.state.branch_id)
    form.state.currency = selectedBranch?.currency || meta.value.default_currency || props.item?.currency || null
  }

  async function submit () {
    if (!form.loading.value && await form.submit(() => props.action === 'create' ? store(form.state) : update(props.item?.id, form.state))) {
      await router.push({ name: 'admin.gift_cards.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(async () => {
    try {
      const response = (await getFormMeta()).data.body
      meta.value = {
        branches: response.branches || [],
        customers: response.customers || [],
        statuses: response.statuses || [],
        default_currency: response.default_currency || null,
      }
      syncCurrencyFromBranch()
    } catch {
    /* empty */
    }
  })

  watch(() => form.state.branch_id, () => {
    syncCurrencyFromBranch()
  })
</script>

<template>
  <BaseForm
    :action="action"
    :loading="form.loading.value"
    resource="gift_cards"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" lg="7">
        <VCard class="mb-6">
          <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-2 font-weight-bold text-h6">
            <VIcon icon="tabler-gift-card" />
            {{ t('giftcard::gift_cards.gift_card') }}
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.code"
                  :error="!!form.errors.value?.code"
                  :error-messages="form.errors.value?.code"
                  :hint="action === 'create'
                    ? `${t('admin::admin.optional')} • Auto generated if left empty`
                    : t('admin::admin.optional')"
                  :label="t('giftcard::attributes.gift_cards.code')"
                  persistent-hint
                  :placeholder="action === 'create' ? t('giftcard::attributes.gift_cards.code') : undefined"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.initial_balance"
                  v-decimal-en
                  :error="!!form.errors.value?.initial_balance"
                  :error-messages="form.errors.value?.initial_balance"
                  :label="t('giftcard::attributes.gift_cards.initial_balance')"
                  :prefix="form.state.currency || ''"
                />
              </VCol>
              <VCol cols="12" md="6">
                <DateTimePicker
                  v-model="form.state.expiry_date"
                  :error="!!form.errors.value?.expiry_date"
                  :error-messages="form.errors.value?.expiry_date"
                  :label="t('giftcard::attributes.gift_cards.expiry_date')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
        <VCard>
          <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-2 font-weight-bold text-h6">
            <VIcon icon="tabler-notes" />
            {{ t('giftcard::attributes.gift_cards.notes') }}
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12">
                <VTextarea
                  v-model="form.state.notes"
                  :error="!!form.errors.value?.notes"
                  :error-messages="form.errors.value?.notes"
                  :label="t('giftcard::attributes.gift_cards.notes')"
                  rows="4"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" lg="5">
        <VCard class="mb-6">
          <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-2 font-weight-bold text-h6">
            <VIcon icon="tabler-adjustments" />
            {{ t('admin::admin.settings') }}
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol v-if="!user?.assigned_to_branch" cols="12" md="6">
                <VSelect
                  v-model="form.state.branch_id"
                  clearable
                  :error="!!form.errors.value?.branch_id"
                  :error-messages="form.errors.value?.branch_id"
                  item-title="name"
                  item-value="id"
                  :items="meta.branches"
                  :label="t('giftcard::attributes.gift_cards.branch_id')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.customer_id"
                  clearable
                  :error="!!form.errors.value?.customer_id"
                  :error-messages="form.errors.value?.customer_id"
                  item-title="name"
                  item-value="id"
                  :items="meta.customers"
                  :label="t('giftcard::attributes.gift_cards.customer_id')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.status"
                  :error="!!form.errors.value?.status"
                  :error-messages="form.errors.value?.status"
                  item-title="name"
                  item-value="id"
                  :items="meta.statuses"
                  :label="t('giftcard::attributes.gift_cards.status')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
