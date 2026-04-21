<script lang="ts" setup>
import {onBeforeMount, ref} from 'vue'
import {type RouteLocationRaw, useRouter} from 'vue-router'
import {useI18n} from 'vue-i18n'
import {useSupplier} from '@/modules/inventory/composables/supplier.ts'
import {useForm} from '@/modules/core/composables/form.ts'
import {useAuth} from '@/modules/auth/composables/auth.ts'

const props = defineProps<{
  item?: Record<string, any> | null
  action: 'update' | 'create'
}>()

const {t} = useI18n()
const {user} = useAuth()

const {getFormMeta, update, store} = useSupplier()
const router = useRouter()

const meta = ref({branches: []})
const form = useForm({
  name: props.item?.name,
  branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
  phone: props.item?.phone,
  email: props.item?.email,
  address: props.item?.address,
})

const submit = async () => {
  if (
    !form.loading.value
    && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
  ) {
    await router.push({name: 'admin.suppliers.index'} as unknown as RouteLocationRaw)
  }
}

onBeforeMount(async () => {
  try {
    const response = (await getFormMeta()).data.body
    meta.value.branches = response.branches
  } catch {
    /* Empty */
  }
})
</script>

<template>
  <BaseForm
    :action="action"
    :loading="form.loading.value"
    resource="suppliers"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20"/>
              <span>{{ t('inventory::suppliers.form.cards.supplier_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name"
                  :error="!!form.errors.value?.name"
                  :error-messages="form.errors.value?.name"
                  :label="t('inventory::attributes.suppliers.name')"
                />
              </VCol>
              <VCol v-if="action=='create' && !user?.assigned_to_branch" cols="12" md="6">
                <VSelect
                  v-model="form.state.branch_id"
                  :error="!!form.errors.value?.branch_id"
                  :error-messages="form.errors.value?.branch_id"
                  :items="meta.branches"
                  :label="t('inventory::attributes.suppliers.branch_id')"
                  item-title="name"
                  item-value="id"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.email"
                  :error="!!form.errors.value?.email"
                  :error-messages="form.errors.value?.email"
                  :label="t('inventory::attributes.suppliers.email')"
                  type="email"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.phone"
                  :error="!!form.errors.value?.phone"
                  :error-messages="form.errors.value?.phone"
                  :label="t('inventory::attributes.suppliers.phone')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.address"
                  :error="!!form.errors.value?.address"
                  :error-messages="form.errors.value?.address"
                  :label="t('inventory::attributes.suppliers.address')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
