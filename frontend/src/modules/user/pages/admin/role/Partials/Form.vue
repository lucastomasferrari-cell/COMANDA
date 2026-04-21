<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useRole } from '@/modules/user/composables/role.ts'
  import FormPermissions from './FormPermissions.vue'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { getFormMeta, update, store } = useRole()
  const router = useRouter()

  const meta = ref({ permissions: [] })
  const form = useForm({
    display_name: props.item?.name || {},
    permissions: props.item?.permissions || [],
  })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.roles.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(async () => {
    try {
      const response = (await getFormMeta()).data.body
      meta.value.permissions = response.permissions
    } catch {
    /* Empty */
    }
  })
</script>

<template>
  <BaseForm
    v-slot="{ currentLanguage }"
    :action="action"
    has-multiple-language
    :loading="form.loading.value"
    resource="roles"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('user::roles.form.cards.role_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.display_name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`display_name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`display_name.${currentLanguage.id}`]"
                  :label="t('user::attributes.roles.display_name') + ` ( ${currentLanguage.name} )`"
                />
              </VCol>

            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <FormPermissions
        v-if="meta.permissions.length>0"
        v-model="form.state.permissions"
        :permissions="meta.permissions"
      />
    </VRow>
  </BaseForm>
</template>
