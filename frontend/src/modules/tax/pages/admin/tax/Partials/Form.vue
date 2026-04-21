<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useTax } from '@/modules/tax/composables/tax.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { getFormMeta, update, store } = useTax()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name || {},
    branch_id: props.action == 'create' ? (user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id) : props.item?.branch?.id,
    code: props.item?.code,
    type: props.item?.type?.id,
    rate: props.item?.rate,
    compound: props.item?.compound || false,
    is_global: props.item?.is_global || false,
    is_active: props.item?.is_active || false,
    order_types: props.item?.order_types || [],
  })

  const meta = ref({ branches: [], types: [], orderTypes: [] })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.taxes.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(() => {
    loadFormData()
  })

  const loadFormData = async (branchId?: number) => {
    try {
      const response = (await getFormMeta(branchId)).data.body
      meta.value.branches = response.branches || meta.value.branches
      meta.value.types = response.types || meta.value.types
      meta.value.orderTypes = response.order_types || meta.value.orderTypes
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
    resource="taxes"
    @submit="submit"
  >
    <VRow>
      <VCol cols="8">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('tax::taxes.form.cards.tax_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('inventory::attributes.units.name') + ` ( ${currentLanguage.name} )`"
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
                  :label="t('tax::attributes.taxes.branch_id')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.code"
                  :error="!!form.errors.value?.code"
                  :error-messages="form.errors.value?.code"
                  :label="t('tax::attributes.taxes.code')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.type"
                  :error="!!form.errors.value?.type"
                  :error-messages="form.errors.value?.type"
                  item-title="name"
                  item-value="id"
                  :items="meta.types"
                  :label="t('tax::attributes.taxes.type')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="4">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-settings-cog" size="20" />
              <span>{{ t('tax::taxes.form.cards.tax_settings') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.state.rate"
                  :error="!!form.errors.value?.rate"
                  :error-messages="form.errors.value?.rate"
                  :label="t('tax::attributes.taxes.rate')"
                />
              </VCol>
              <template v-if="form.state.type === 'exclusive'">
                <VCol cols="12">
                  <VSelect
                    v-model="form.state.order_types"
                    chips
                    :error="!!form.errors.value?.order_types"
                    :error-messages="form.errors.value?.order_types"
                    item-title="name"
                    item-value="id"
                    :items="meta.orderTypes"
                    :label="t('tax::attributes.taxes.order_types')"
                    multiple
                  />
                </VCol>
                <VCol cols="4">
                  <VCheckbox
                    v-model="form.state.compound"
                    :label="t('tax::attributes.taxes.compound')"
                  />
                </VCol>
              </template>
              <VCol cols="4">
                <VCheckbox
                  v-model="form.state.is_global"
                  :label="t('tax::attributes.taxes.is_global')"
                />
              </VCol>
              <VCol cols="4">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('tax::attributes.taxes.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
