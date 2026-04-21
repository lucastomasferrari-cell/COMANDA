<script lang="ts" setup>
  import { onBeforeMount, ref, watch } from 'vue'
  import { type RouteLocationRaw, useRouter } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useOnlineMenu } from '@/modules/menu/composables/onlineMenu.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { getFormMeta, update, store } = useOnlineMenu()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name || {},
    slug: props.item?.slug,
    menu_id: props.item?.menu?.id,
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    is_active: props.item?.is_active || false,
  })

  const meta = ref({ branches: [], menus: [] })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.online_menus.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(() => {
    loadFormData()
    if (form.state.branch_id) {
      loadFormData(form.state.branch_id)
    }
  })

  watch(() =>
          form.state.branch_id,
        newValue => {
          form.state.menu_id = null
          meta.value.menus = []
          loadFormData(newValue)
        },
  )

  const loadFormData = async (branchId?: number) => {
    try {
      const response = (await getFormMeta(branchId)).data.body
      meta.value.branches = response.branches || meta.value.branches
      if (branchId) {
        meta.value.menus = response.menus
      }
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
    resource="online_menus"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('menu::online_menus.form.cards.online_menu_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('menu::attributes.online_menus.name') + ` ( ${currentLanguage.name} )`"
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
                  :label="t('menu::attributes.online_menus.branch_id')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.slug"
                  :error="!!form.errors.value?.slug"
                  :error-messages="form.errors.value?.slug"
                  :label="t('menu::attributes.online_menus.slug')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.menu_id"
                  :error="!!form.errors.value?.menu_id"
                  :error-messages="form.errors.value?.menu_id"
                  item-title="name"
                  item-value="id"
                  :items="meta.menus"
                  :label="t('menu::attributes.online_menus.menu_id')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('menu::attributes.online_menus.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
