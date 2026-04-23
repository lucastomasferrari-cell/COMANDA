<script lang="ts" setup>
  import { onBeforeMount, ref } from 'vue'
  import { type RouteLocationRaw, useRouter } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { usePrinter } from '@/modules/printer/composables/printer.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { getFormMeta, update, store } = usePrinter()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name || {},
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    options: {
      qintrix_id: null,
      paper_size: '80mm',
      ...props.item?.options,
    },
    is_active: props.item?.is_active ?? true,
  })

  const meta = ref({
    branches: [],
    paperSizes: [],
  })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.configuracion.operacion.impresion' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(() => {
    loadFormData()
  })

  const loadFormData = async (branchId?: number) => {
    try {
      const response = (await getFormMeta(branchId)).data.body
      meta.value.branches = response.branches
      meta.value.paperSizes = response.paper_sizes
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
    resource="printers"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="5">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('printer::printers.form.cards.printer_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('printer::attributes.printers.name') + ` ( ${currentLanguage.name} )`"
                />
              </VCol>
              <VCol v-if="false" cols="12">
                <VSelect
                  v-model="form.state.branch_id"
                  :error="!!form.errors.value?.branch_id"
                  :error-messages="form.errors.value?.branch_id"
                  item-title="name"
                  item-value="id"
                  :items="meta.branches"
                  :label="t('printer::attributes.printers.branch_id')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-if="action !== 'create'"
                  v-model="form.state.is_active"
                  :label="t('printer::attributes.printers.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="7">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-affiliate" size="20" />
              <span>{{ t('printer::printers.form.cards.connection_options') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.state.options.qintrix_id"
                  :error="!!form.errors.value?.['options.qintrix_id']"
                  :error-messages="form.errors.value?.['options.qintrix_id']"
                  :label="t('printer::attributes.printers.options.qintrix_id')"
                />
              </VCol>
              <VCol cols="12">
                <VSelect
                  v-model="form.state.options.paper_size"
                  :error="!!form.errors.value?.['options.paper_size']"
                  :error-messages="form.errors.value?.['options.paper_size']"
                  item-title="name"
                  item-value="id"
                  :items="meta.paperSizes"
                  :label="t('printer::attributes.printers.options.paper_size')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
