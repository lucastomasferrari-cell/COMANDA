<script lang="ts" setup>
import {computed, onBeforeMount, ref, watch} from 'vue'
import {type RouteLocationRaw, useRouter} from 'vue-router'
import {useI18n} from 'vue-i18n'
import {usePurchase} from '@/modules/inventory/composables/purchase.ts'
import {useForm} from '@/modules/core/composables/form.ts'
import {useAuth} from '@/modules/auth/composables/auth.ts'
import {useAppStore} from '@/modules/core/stores/appStore.ts'
import Items from './Items.vue'
import Summary from './Summary.vue'

const props = defineProps<{
  item?: Record<string, any> | null
  action: 'update' | 'create'
}>()

const {t} = useI18n()
const {user} = useAuth()
const {getFormMeta, update, store} = usePurchase()
const router = useRouter()
const appStore = useAppStore()

const form = useForm({
  branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
  supplier_id: props.item?.supplier?.id,
  notes: props.item?.notes,
  expected_at: props.item?.expected_at,
  items: props.item?.items
    ? props.item?.items.map((item: Record<string, any>) => ({
      ...item,
      unit_cost: item.unit_cost.original.amount,
      ingredient_id: item.ingredient.id,
    }))
    : [],
  discount: props.item?.discount?.original?.amount,
  tax: props.item?.tax?.original?.amount,
})

const currency = computed(() => {
  if (form.state.branch_id) {
    const selectedBranch = meta.value.branches.find((item: Record<string, any>) => item.id === form.state.branch_id)
    if (selectedBranch) {
      return selectedBranch.currency
    }
  }
  return appStore.currency
})

const meta = ref({branches: [] as Record<string, any>[], ingredients: [], suppliers: []})

const submit = async () => {
  if (
    !form.loading.value
    && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
  ) {
    await router.push({name: 'admin.purchases.index'} as unknown as RouteLocationRaw)
  }
}

onBeforeMount(() => {
  if (form.state.items.length === 0) {
    form.state.items.push({
      ingredient_id: null,
      quantity: null,
      unit_cost: null,
    })
  }
  loadFormData()
  if (form.state.branch_id) {
    loadFormData(form.state.branch_id)
  }
})

watch(() => form.state.branch_id, newValue => {
  form.state.supplier_id = null
  for (const item of form.state.items) {
    item.ingredient_id = null
  }
  meta.value.ingredients = []
  meta.value.suppliers = []
  loadFormData(newValue)
})

const loadFormData = async (branchId?: number) => {
  try {
    const response = (await getFormMeta(branchId)).data.body
    meta.value.branches = response.branches || meta.value.branches
    if (branchId) {
      meta.value.ingredients = response.ingredients
      meta.value.suppliers = response.suppliers
    }
  } catch {
    /* Empty */
  }
}
</script>

<template>
  <BaseForm
    :action="action"
    :loading="form.loading.value"
    resource="purchases"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="3">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20"/>
              <span>{{ t('inventory::purchases.form.cards.purchase_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol v-if="action=='create' && !user?.assigned_to_branch" cols="12">
                <VSelect
                  v-model="form.state.branch_id"
                  :error="!!form.errors.value?.branch_id"
                  :error-messages="form.errors.value?.branch_id"
                  :items="meta.branches"
                  :label="t('inventory::attributes.purchases.branch_id')"
                  item-title="name"
                  item-value="id"
                />
              </VCol>
              <VCol cols="12">
                <VSelect
                  v-model="form.state.supplier_id"
                  :error="!!form.errors.value?.supplier_id"
                  :error-messages="form.errors.value?.supplier_id"
                  :items="meta.suppliers"
                  :label="t('inventory::attributes.purchases.supplier_id')"
                  item-title="name"
                  item-value="id"
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model.number="form.state.tax"
                  :error="!!form.errors.value?.tax"
                  :error-messages="form.errors.value?.tax"
                  :label="t('inventory::attributes.purchases.tax')"
                  hide-details
                  min="0"
                  step="0.01"
                  type="number"
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model.number="form.state.discount"
                  :error="!!form.errors.value?.discount"
                  :error-messages="form.errors.value?.discount"
                  :label="t('inventory::attributes.purchases.discount')"
                  hide-details
                  min="0"
                  step="0.01"
                  type="number"
                />
              </VCol>
              <VCol cols="12">
                <DatePicker
                  v-model="form.state.expected_at"
                  :error="!!form.errors.value?.expected_at"
                  :error-messages="form.errors.value?.expected_at"
                  :label="t('inventory::attributes.purchases.expected_at')"
                  :min="new Date().toLocaleDateString('en-CA')"
                  clearable
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model.number="form.state.notes"
                  :error="!!form.errors.value?.notes"
                  :error-messages="form.errors.value?.notes"
                  :label="t('inventory::attributes.purchases.notes')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="9">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-clipboard-list" size="20"/>
              <span>{{ t('inventory::purchases.form.cards.purchase_items') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12">
                <Items :currency="currency" :form="form" :ingredients="meta.ingredients"/>
              </VCol>
              <VCol cols="12">
                <Summary :currency="currency" :form="form"/>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
