<script lang="ts" setup>
import {computed, onBeforeMount, ref} from 'vue'
import {type RouteLocationRaw, useRouter} from 'vue-router'
import {useI18n} from 'vue-i18n'
import {useIngredient} from '@/modules/inventory/composables/ingredient.ts'
import {useForm} from '@/modules/core/composables/form.ts'
import {useAuth} from '@/modules/auth/composables/auth.ts'
import {useAppStore} from '@/modules/core/stores/appStore.ts'

const props = defineProps<{
  item?: Record<string, any> | null
  action: 'update' | 'create'
}>()

const {t} = useI18n()
const {user} = useAuth()
const {getFormMeta, update, store} = useIngredient()
const router = useRouter()
const appStore = useAppStore()

const form = useForm({
  name: props.item?.name || {},
  branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
  unit_id: props.item?.unit_id,
  cost_per_unit: props.item?.cost_per_unit?.amount,
  alert_quantity: props.item?.alert_quantity,
  is_returnable: props.item?.is_returnable,
})

const meta = ref({branches: [] as Record<string, any>[], units: []})
const currency = computed(() => {
  if (form.state.branch_id) {
    const selectedBranch: Record<string, any> | undefined = meta.value.branches.find((item: Record<string, any>) => item.id === form.state.branch_id)
    if (selectedBranch) {
      return selectedBranch?.currency
    }
  }
  return appStore.currency
})

const submit = async () => {
  if (
    !form.loading.value
    && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
  ) {
    await router.push({name: 'admin.ingredients.index'} as unknown as RouteLocationRaw)
  }
}

onBeforeMount(async () => {
  try {
    const response = (await getFormMeta()).data.body
    meta.value.branches = response.branches
    meta.value.units = response.units
  } catch {
    /* Empty */
  }
})
</script>

<template>
  <BaseForm
    v-slot="{ currentLanguage }"
    :action="action"
    :loading="form.loading.value"
    has-multiple-language
    resource="ingredients"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20"/>
              <span>{{ t('inventory::ingredients.form.cards.ingredient_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('inventory::attributes.ingredients.name') + ` ( ${currentLanguage.name} )`"
                />
              </VCol>
              <VCol v-if="action=='create' && !user?.assigned_to_branch" cols="12" md="6">
                <VSelect
                  v-model="form.state.branch_id"
                  :error="!!form.errors.value?.branch_id"
                  :error-messages="form.errors.value?.branch_id"
                  :items="meta.branches"
                  :label="t('inventory::attributes.ingredients.branch_id')"
                  item-title="name"
                  item-value="id"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.unit_id"
                  :error="!!form.errors.value?.unit_id"
                  :error-messages="form.errors.value?.unit_id"
                  :items="meta.units"
                  :label="t('inventory::attributes.ingredients.unit_id')"
                  item-title="name"
                  item-value="id"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.cost_per_unit"
                  :error="!!form.errors.value?.cost_per_unit"
                  :error-messages="form.errors.value?.cost_per_unit"
                  :label="t('inventory::attributes.ingredients.cost_per_unit')"
                  :prefix="currency"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.alert_quantity"
                  :error="!!form.errors.value?.alert_quantity"
                  :error-messages="form.errors.value?.alert_quantity"
                  :label="t('inventory::attributes.ingredients.alert_quantity')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="form.state.is_returnable"
                  :label="t('inventory::attributes.ingredients.is_returnable')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
