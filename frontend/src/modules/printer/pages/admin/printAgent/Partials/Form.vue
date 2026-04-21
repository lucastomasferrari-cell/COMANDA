<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { usePrintAgent } from '@/modules/printer/composables/printAgent.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { getFormMeta, update, store } = usePrintAgent()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name || {},
    api_key: props.item?.api_key,
    host: props.item?.host,
    port: props.item?.port,
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    is_active: props.item?.is_active || false,
  })

  const meta = ref({
    branches: [],
  })

  async function submit () {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.print_agents.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(() => {
    loadFormData()
  })

  async function loadFormData (branchId?: number) {
    try {
      const response = (await getFormMeta(branchId)).data.body
      meta.value.branches = response.branches
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
    resource="print_agents"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="6">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('printer::print_agents.form.cards.print_agent_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" :md="action=='create' && !user?.assigned_to_branch?6:12">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('printer::attributes.print_agents.name') + ` ( ${currentLanguage.name} )`"
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
                  :label="t('printer::attributes.print_agents.branch_id')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('printer::attributes.print_agents.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="6">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-affiliate" size="20" />
              <span>{{ t('printer::print_agents.form.cards.qintrix_integration') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.state.api_key"
                  :error="!!form.errors.value?.api_key"
                  :error-messages="form.errors.value?.api_key"
                  :label="t('printer::attributes.print_agents.api_key')"
                />
              </VCol>
              <VCol cols="12" md="8">
                <VTextField
                  v-model="form.state.host"
                  :error="!!form.errors.value?.host"
                  :error-messages="form.errors.value?.host"
                  :label="t('printer::attributes.print_agents.host')"
                />
              </VCol>
              <VCol cols="12" md="4">
                <VTextField
                  v-model="form.state.port"
                  :error="!!form.errors.value?.port"
                  :error-messages="form.errors.value?.port"
                  :label="t('printer::attributes.print_agents.port')"
                  type="number"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
