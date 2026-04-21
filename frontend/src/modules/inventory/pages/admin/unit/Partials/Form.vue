<script lang="ts" setup>
import {onBeforeMount, ref} from 'vue'
import {type RouteLocationRaw, useRouter} from 'vue-router'
import {useI18n} from 'vue-i18n'
import {useUnit} from '@/modules/inventory/composables/unit.ts'
import {useForm} from '@/modules/core/composables/form.ts'

const props = defineProps<{
  item?: Record<string, any> | null
  action: 'update' | 'create'
}>()

const {t} = useI18n()

const {getFormMeta, update, store} = useUnit()
const router = useRouter()

const meta = ref({types: []})
const form = useForm({
  name: props.item?.name || {},
  symbol: props.item?.symbol || {},
  type: props.item?.type?.id,
})

const submit = async () => {
  if (
    !form.loading.value
    && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
  ) {
    await router.push({name: 'admin.units.index'} as unknown as RouteLocationRaw)
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
    :loading="form.loading.value"
    has-multiple-language
    resource="units"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20"/>
              <span>{{ t('inventory::units.form.cards.unit_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="4">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('inventory::attributes.units.name') + ` ( ${currentLanguage.name} )`"
                />
              </VCol>
              <VCol cols="12" md="4">
                <VTextField
                  v-model="form.state.symbol[currentLanguage.id]"
                  :error="!!form.errors.value?.[`symbol.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`symbol.${currentLanguage.id}`]"
                  :label="t('inventory::attributes.units.symbol') + ` ( ${currentLanguage.name} )`"
                />
              </VCol>
              <VCol cols="12" md="4">
                <VSelect
                  v-model="form.state.type"
                  :error="!!form.errors.value?.type"
                  :error-messages="form.errors.value?.type"
                  :items="meta.types"
                  :label="t('inventory::attributes.units.type')"
                  item-title="name"
                  item-value="id"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
