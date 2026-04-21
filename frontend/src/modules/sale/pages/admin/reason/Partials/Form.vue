<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useReason } from '@/modules/sale/composables/reason.ts'
  import { useForm } from '@/modules/core/composables/form.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { update, store, getFormMeta } = useReason()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name || {},
    type: props.item?.type?.id,
    is_active: props.item?.is_active || false,
  })

  const meta = ref({ types: [] })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.reasons.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(async () => {
    try {
      const response = (await getFormMeta()).data.body
      meta.value.types = response.types
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
    resource="reasons"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('order::reasons.form.cards.reason_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('order::attributes.reasons.name') + ` ( ${currentLanguage.name} )`"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.type"
                  :error="!!form.errors.value?.type"
                  :error-messages="form.errors.value?.type"
                  item-title="name"
                  item-value="id"
                  :items="meta.types"
                  :label="t('order::attributes.reasons.type')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('order::attributes.reasons.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
