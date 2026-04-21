<script lang="ts" setup>
  import type { PosForm } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, onBeforeMount, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useTable } from '@/modules/seatingPlan/composables/table.ts'

  const props = defineProps<{
    modelValue: boolean
    tableId: number
    posForm: PosForm
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'refresh'): void
  }>()

  const { t } = useI18n()
  const { merge, getMergeMeta } = useTable()

  const meta = ref({ tables: [], types: [] })

  const dialogModel = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val),
  })

  const form = useForm({
    table_ids: [],
    register_id: props.posForm.registerId,
    session_id: props.posForm.sessionId,
    branch_id: props.posForm.branchId,
    type: null,
  })

  onBeforeMount(async () => {
    try {
      const response = (await getMergeMeta(props.tableId)).data.body
      meta.value.tables = response.tables
      meta.value.types = response.types
    } catch {
    /* Empty */
    }
  })

  const close = () => emit('update:modelValue', false)

  async function submit () {
    if (
      !form.loading.value
      && await form.submit(() => merge(props.tableId, form.state))
    ) {
      emit('refresh')
      close()
    }
  }
</script>

<template>
  <VDialog
    v-model="dialogModel"
    max-width="500"
  >
    <VCard>
      <VCardTitle class="border-b pb-2  d-flex align-center gap-1 font-weight-bold text-h6">
        <VIcon icon="tabler-arrow-merge" size="20" />
        {{ t("seatingplan::tables.merge_table") }}
      </VCardTitle>

      <VCardText>
        <VForm @submit.prevent="submit">
          <VRow>
            <VCol cols="12">
              <VSelect
                v-model="form.state.type"
                :error="!!form.errors.value?.type"
                :error-messages="form.errors.value?.type"
                item-title="name"
                item-value="id"
                :items="meta.types"
                :label="t('seatingplan::attributes.table_merges.type')"
              />
            </VCol>
            <VCol cols="12">
              <VSelect
                v-model="form.state.table_ids"
                chips
                :error="!!form.errors.value?.table_ids"
                :error-messages="form.errors.value?.table_ids"
                item-title="name"
                item-value="id"
                :items="meta.tables"
                :label="t('seatingplan::attributes.table_merges.table_ids')"
                multiple
              />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn color="default" :disabled="form.loading.value" @click="close">
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
        <VBtn
          color="primary"
          :disabled="form.state.table_ids.length === 0||!form.state.type"
          :loading="form.loading.value"
          @click="submit"
        >
          {{ t('admin::admin.buttons.merge') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
