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

  // Single-branch: branch_id queda oculto y se auto-asigna al del usuario
  // (o 1, forzado por ValidateSingleBranchInvariant middleware).
  const form = useForm({
    name: props.item?.name || {},
    description: props.item?.description || {},
    sku: props.item?.sku,
    sku_locked: props.item?.sku_locked || false,
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    is_active: props.item?.is_active ?? true,
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
              <VCol cols="12">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('menu::attributes.menus.name')"
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="form.state.sku"
                  clearable
                  :error="!!form.errors.value?.sku"
                  :error-messages="form.errors.value?.sku"
                  :hint="t('menu::attributes.menus.sku_hint')"
                  :label="t('menu::attributes.menus.sku')"
                  persistent-hint
                  :readonly="form.state.sku_locked"
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
                  :label="t('menu::attributes.menus.description')"
                  rows="4"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-if="action !== 'create'"
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
