<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { usePosRegister } from '@/modules/pos/composables/posRegister.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { getFormMeta, update, store } = usePosRegister()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name || {},
    note: props.item?.note || {},
    code: props.item?.code,
    invoice_printer_id: props.item?.invoice_printer?.id,
    bill_printer_id: props.item?.bill_printer?.id,
    // delivery_printer_id: props.item?.delivery_printer?.id, // // TODO: Remove comment on support delivery
    waiter_printer_id: props.item?.waiter_printer?.id,
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    is_active: props.item?.is_active || false,
  })

  const meta = ref({ branches: [], printers: [] })

  async function submit () {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.pos_registers.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(() => {
    loadFormData()
    if (form.state.branch_id) {
      loadFormData(form.state.branch_id)
    }
  })

  watch(() => form.state.branch_id, newValue => {
    form.state.invoice_printer_id = null
    form.state.bill_printer_id = null
    meta.value.printers = []
    loadFormData(newValue)
  })

  async function loadFormData (branchId?: number) {
    try {
      const response = (await getFormMeta(branchId)).data.body
      meta.value.branches = response.branches || meta.value.branches
      if (branchId) {
        meta.value.printers = response.printers
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
    resource="pos_registers"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VRow>
          <VCol cols="8">
            <VCard>
              <VCardTitle class="d-flex justify-space-between align-center mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-info-circle" size="20" />
                  <span>{{ t('pos::pos_registers.form.cards.pos_register_information') }}</span>
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.name[currentLanguage.id]"
                      :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                      :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                      :label="t('pos::attributes.pos_registers.name') + ` ( ${currentLanguage.name} )`"
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
                      :label="t('pos::attributes.pos_registers.branch_id')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.code"
                      :error="!!form.errors.value?.code"
                      :error-messages="form.errors.value?.code"
                      :label="t('pos::attributes.pos_registers.code')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.note[currentLanguage.id]"
                      clearable
                      :error="!!form.errors.value?.[`note.${currentLanguage.id}`]"
                      :error-messages="form.errors.value?.[`note.${currentLanguage.id}`]"
                      :label="t('pos::attributes.pos_registers.note') + ` ( ${currentLanguage.name} )`"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VCheckbox
                      v-model="form.state.is_active"
                      :label="t('pos::attributes.pos_registers.is_active')"
                    />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="4">
            <VCard>
              <VCardTitle class="d-flex justify-space-between align-center mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-printer" size="20" />
                  <span>{{ t('pos::pos_registers.form.cards.printers') }}</span>
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12">
                    <VSelect
                      v-model="form.state.invoice_printer_id"
                      :error="!!form.errors.value?.invoice_printer_id"
                      :error-messages="form.errors.value?.invoice_printer_id"
                      item-title="name"
                      item-value="id"
                      :items="meta.printers"
                      :label="t('pos::attributes.pos_registers.invoice_printer_id')"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VSelect
                      v-model="form.state.bill_printer_id"
                      :error="!!form.errors.value?.bill_printer_id"
                      :error-messages="form.errors.value?.bill_printer_id"
                      item-title="name"
                      item-value="id"
                      :items="meta.printers"
                      :label="t('pos::attributes.pos_registers.bill_printer_id')"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VSelect
                      v-model="form.state.waiter_printer_id"
                      :error="!!form.errors.value?.waiter_printer_id"
                      :error-messages="form.errors.value?.waiter_printer_id"
                      item-title="name"
                      item-value="id"
                      :items="meta.printers"
                      :label="t('pos::attributes.pos_registers.waiter_printer_id')"
                    />
                  </VCol>
                  <!--                  <VCol cols="12">-->
                  <!--                    <VSelect-->
                  <!--                      v-model="form.state.delivery_printer_id"-->
                  <!--                      :error="!!form.errors.value?.delivery_printer_id"-->
                  <!--                      :error-messages="form.errors.value?.delivery_printer_id"-->
                  <!--                      item-title="name"-->
                  <!--                      item-value="id"-->
                  <!--                      :items="meta.printers"-->
                  <!--                      :label="t('pos::attributes.pos_registers.delivery_printer_id')"-->
                  <!--                    />-->
                  <!--                  </VCol>-->
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </BaseForm>
</template>
