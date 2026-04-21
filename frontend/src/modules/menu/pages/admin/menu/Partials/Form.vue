<script lang="ts" setup>
  import { onBeforeMount, ref } from 'vue'
  import { type RouteLocationRaw, useRouter } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useMenu } from '@/modules/menu/composables/menu.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { getFormMeta, update, store } = useMenu()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name || {},
    description: props.item?.description || {},
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    is_active: props.item?.is_active || false,
  })

  const meta = ref({ branches: [] })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.menus.index' } as unknown as RouteLocationRaw)
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
    v-slot="{ currentLanguage }"
    :action="action"
    has-multiple-language
    :loading="form.loading.value"
    resource="menus"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('menu::menus.form.cards.menu_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('menu::attributes.menus.name') + ` ( ${currentLanguage.name} )`"
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
                  :label="t('menu::attributes.menus.branch_id')"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="form.state.description[currentLanguage.id]"
                  auto-grow
                  clearable
                  :counter="1000"
                  :error="!!form.errors.value?.[`description.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`description.${currentLanguage.id}`]"
                  :label="t('menu::attributes.menus.description') + ` ( ${currentLanguage.name} )`"
                  rows="4"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('menu::attributes.menus.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
