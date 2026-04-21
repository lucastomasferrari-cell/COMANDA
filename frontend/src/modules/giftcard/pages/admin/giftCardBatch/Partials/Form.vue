<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { onBeforeMount, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import BaseForm from '@/modules/core/components/Form/BaseForm.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useGiftCardBatch } from '@/modules/giftcard/composables/giftCardBatch.ts'

  const { t } = useI18n()
  const { user } = useAuth()
  const { getFormMeta, store } = useGiftCardBatch()
  const router = useRouter()

  const form = useForm({
    name: {} as Record<string, string>,
    prefix: null,
    quantity: 1,
    value: null,
    currency: null as string | null,
    branch_id: (user?.assigned_to_branch ? user.branch_id : null) as number | null,
  })

  type GiftCardBatchFormMeta = {
    branches: Array<{ id: number, name: string, currency?: string | null }>
    default_currency: string | null
  }

  const meta = ref<GiftCardBatchFormMeta>({
    branches: [],
    default_currency: null,
  })

  function syncCurrencyFromBranch () {
    const selectedBranch = meta.value.branches.find((branch: Record<string, any>) => branch.id === form.state.branch_id)
    form.state.currency = selectedBranch?.currency || meta.value.default_currency || null
  }

  async function submit () {
    if (!form.loading.value && await form.submit(() => store(form.state))) {
      await router.push({ name: 'admin.gift_card_batches.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(async () => {
    try {
      const response = (await getFormMeta()).data.body
      meta.value.branches = response.branches || []
      meta.value.default_currency = response.default_currency || null
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
    v-slot="{ currentLanguage }"
    action="create"
    has-multiple-language
    :loading="form.loading.value"
    resource="gift_card_batches"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle>{{ t('giftcard::gift_card_batches.gift_card_batch') }}</VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('giftcard::attributes.gift_card_batches.name') + ` (${currentLanguage.name})`"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.prefix"
                  :error="!!form.errors.value?.prefix"
                  :error-messages="form.errors.value?.prefix"
                  :label="t('giftcard::attributes.gift_card_batches.prefix')"
                />
              </VCol>
              <VCol v-if="!user?.assigned_to_branch" cols="12" md="6">
                <VSelect
                  v-model="form.state.branch_id"
                  clearable
                  :error="!!form.errors.value?.branch_id"
                  :error-messages="form.errors.value?.branch_id"
                  item-title="name"
                  item-value="id"
                  :items="meta.branches"
                  :label="t('giftcard::attributes.gift_card_batches.branch_id')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.quantity"
                  :error="!!form.errors.value?.quantity"
                  :error-messages="form.errors.value?.quantity"
                  :label="t('giftcard::attributes.gift_card_batches.quantity')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.value"
                  v-decimal-en
                  :error="!!form.errors.value?.value"
                  :error-messages="form.errors.value?.value"
                  :label="t('giftcard::attributes.gift_card_batches.value')"
                  :prefix="form.state.currency || ''"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
