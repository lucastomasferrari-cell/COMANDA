<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import BaseForm from '@/modules/core/components/Form/BaseForm.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useUser } from '@/modules/user/composables/user.ts'
  import KitchenConfiguration from './KitchenConfiguration.vue'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()

  const { getFormMeta, update, store } = useUser()
  const router = useRouter()

  const meta = ref<{
    roles: Record<string, any>[]
    branches: Record<string, any>[]
    genders: Record<string, any>[]
    categories: Record<string, any>[]
    printers: Record<string, any>[]
    branchAvailableRoles: string[]
  }>({
    roles: [],
    branches: [],
    genders: [],
    categories: [],
    printers: [],
    branchAvailableRoles: [],
  })

  const form = useForm({
    name: props.item?.name,
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    username: props.item?.username,
    email: props.item?.email,
    gender: props.item?.gender.id,
    role: props.item?.role?.id,
    is_active: props.item?.is_active || false,
    password: null,
    password_confirmation: null,
    printer_id: props.item?.printer?.id,
    category_slugs: props.item?.category_slugs,
  })

  async function submit () {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.users.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(() => {
    loadFormData()
    if (form.state.branch_id) {
      loadFormData(form.state.branch_id)
    }
  })

  watch(() => form.state.branch_id,
        newValue => {
          form.state.printer_id = null
          meta.value.printers = []
          loadFormData(newValue)
        })

  async function loadFormData (branchId?: number) {
    try {
      const response = (await getFormMeta(branchId)).data.body
      meta.value.branches = response.branches || meta.value.branches
      meta.value.roles = response.roles || meta.value.roles
      meta.value.genders = response.genders || meta.value.genders
      meta.value.categories = response.categories || meta.value.categories
      meta.value.branchAvailableRoles = response.branch_available_roles || meta.value.branchAvailableRoles
      if (branchId) {
        meta.value.printers = response.printers
      }
    } catch {
    /* Empty */
    }
  }

  const roleKey = computed<string | null>(() => {
    if (form.state.role) {
      const role = meta.value.roles.find(
        (role: Record<string, any>) => role.id === form.state.role,
      )
      if (role) {
        return (role as Record<string, any>).key
      }
    }
    return null
  })
</script>

<template>
  <BaseForm
    :action="action"
    :loading="form.loading.value"
    resource="users"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VRow>
          <VCol cols="12" md="8">
            <VCard>
              <VCardTitle class="d-flex justify-space-between align-center mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-info-circle" size="20" />
                  <span>{{ t('user::users.form.cards.user_information') }}</span>
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.name"
                      :error="!!form.errors.value?.name"
                      :error-messages="form.errors.value?.name"
                      :label="t('user::attributes.users.name')"
                    />
                  </VCol>
                  <VCol
                    v-if="(action=='create' && !user?.assigned_to_branch) || (action == 'update' && roleKey && !props.item?.branch?.id && meta.branchAvailableRoles.includes(roleKey))"
                    cols="12"
                    md="6"
                  >
                    <VSelect
                      v-model="form.state.branch_id"
                      :error="!!form.errors.value?.branch_id"
                      :error-messages="form.errors.value?.branch_id"
                      item-title="name"
                      item-value="id"
                      :items="meta.branches"
                      :label="t('user::attributes.users.branch_id')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.username"
                      :error="!!form.errors.value?.username"
                      :error-messages="form.errors.value?.username"
                      :label="t('user::attributes.users.username')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.email"
                      :error="!!form.errors.value?.email"
                      :error-messages="form.errors.value?.email"
                      :label="t('user::attributes.users.email')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VSelect
                      v-model="form.state.gender"
                      :error="!!form.errors.value?.gender"
                      :error-messages="form.errors.value?.gender"
                      item-title="name"
                      item-value="id"
                      :items="meta.genders"
                      :label="t('user::attributes.users.gender')"
                    />
                  </VCol>
                  <VCol v-if="item?.is_main_user!=true" cols="12" md="6">
                    <VSelect
                      v-model="form.state.role"
                      :error="!!form.errors.value?.role"
                      :error-messages="form.errors.value?.role"
                      item-title="name"
                      item-value="id"
                      :items="meta.roles"
                      :label="t('user::attributes.users.role')"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VCheckbox
                      v-model="form.state.is_active"
                      :disabled="item?.is_main_user==true"
                      :label="t('user::attributes.users.is_active')"
                    />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12" md="4">
            <VCard>
              <VCardTitle class="d-flex justify-space-between align-center mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-key" size="20" />
                  <span>{{ t('user::users.form.cards.security') }}</span>
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12">
                    <VTextField
                      v-model="form.state.password"
                      :error="!!form.errors.value?.password"
                      :error-messages="form.errors.value?.password"
                      :label="t('user::attributes.users.password')"
                      type="password"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextField
                      v-model="form.state.password_confirmation"
                      :error="!!form.errors.value?.password_confirmation"
                      :error-messages="form.errors.value?.password_confirmation"
                      :label="t('user::attributes.users.password_confirmation')"
                      type="password"
                    />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
          <KitchenConfiguration v-if="roleKey==='kitchen'" :form="form" :meta="meta" />

        </VRow>
      </VCol>
    </VRow>
  </BaseForm>
</template>
