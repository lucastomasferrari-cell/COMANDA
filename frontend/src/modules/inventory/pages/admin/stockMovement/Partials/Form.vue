<script lang="ts" setup>
import {onBeforeMount, ref, watch} from 'vue'
import {type RouteLocationRaw, useRouter} from 'vue-router'
import {useI18n} from 'vue-i18n'
import {useStockMovement} from '@/modules/inventory/composables/stockMovement.ts'
import {useForm} from '@/modules/core/composables/form.ts'
import {useAuth} from '@/modules/auth/composables/auth.ts'

const props = defineProps<{
  item?: Record<string, any> | null
  action: 'update' | 'create'
}>()

const {t} = useI18n()
const {user} = useAuth()
const {getFormMeta, update, store} = useStockMovement()
const router = useRouter()

const form = useForm({
  branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
  ingredient_id: props.item?.ingredient?.id,
  type: props.item?.type?.id,
  quantity: props.item?.quantity,
  note: props.item?.note,
})

const meta = ref({branches: [], types: [], ingredients: []})

const submit = async () => {
  if (
    !form.loading.value
    && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
  ) {
    await router.push({name: 'admin.stock_movements.index'} as unknown as RouteLocationRaw)
  }
}

onBeforeMount(() => {
  loadFormData()
  if (form.state.branch_id) {
    loadFormData(form.state.branch_id)
  }
})

watch(() => form.state.branch_id, newValue => {
  form.state.ingredient_id = null
  meta.value.ingredients = []
  loadFormData(newValue)
})

const loadFormData = async (branchId?: number) => {
  try {
    const response = (await getFormMeta(branchId)).data.body
    meta.value.branches = response.branches || meta.value.branches
    meta.value.types = response.types || meta.value.types
    if (branchId) {
      meta.value.ingredients = response.ingredients
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
    resource="stock-movements"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20"/>
              <span>{{ t('inventory::stock_movements.form.cards.stock_movement_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol v-if="action=='create' && !user?.assigned_to_branch" cols="12" md="6">
                <VSelect
                  v-model="form.state.branch_id"
                  :error="!!form.errors.value?.branch_id"
                  :error-messages="form.errors.value?.branch_id"
                  :items="meta.branches"
                  :label="t('inventory::attributes.stock_movements.branch_id')"
                  item-title="name"
                  item-value="id"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.type"
                  :error="!!form.errors.value?.type"
                  :error-messages="form.errors.value?.type"
                  :items="meta.types"
                  :label="t('inventory::attributes.stock_movements.type')"
                  item-title="name"
                  item-value="id"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.ingredient_id"
                  :error="!!form.errors.value?.ingredient_id"
                  :error-messages="form.errors.value?.ingredient_id"
                  :items="meta.ingredients"
                  :label="t('inventory::attributes.stock_movements.ingredient_id')"
                  item-title="name"
                  item-value="id"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.quantity"
                  :error="!!form.errors.value?.quantity"
                  :error-messages="form.errors.value?.quantity"
                  :label="t('inventory::attributes.stock_movements.quantity')"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="form.state.note"
                  :counter="1000"
                  :error="!!form.errors.value?.note"
                  :error-messages="form.errors.value?.note"
                  :label="t('inventory::attributes.stock_movements.note')"
                  auto-grow
                  clearable
                  rows="4"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

    </VRow>
  </BaseForm>
</template>
