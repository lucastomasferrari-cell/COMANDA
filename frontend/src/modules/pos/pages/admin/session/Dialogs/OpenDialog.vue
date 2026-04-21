<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { usePosSession } from '@/modules/pos/composables/posSession.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    modelValue: boolean
    branchId?: number | null
    posRegisterId?: number | null
  }>()
  const emit = defineEmits(['update:modelValue', 'saved'])

  const { t } = useI18n()
  const { user } = useAuth()
  const { open, getFormMeta } = usePosSession()
  const form = useForm({
    branch_id: user?.assigned_to_branch ? user.branch_id : (props.branchId || null),
    opening_float: null,
    notes: null,
    pos_register_id: props.posRegisterId || null,
  })

  const meta = ref({ branches: [], posRegisters: [] })

  function close () {
    emit('update:modelValue', false)
  }

  const submit = async () => {
    if (!form.loading.value) {
      const response = await form.submit(() => open(form.state), true)
      if (response && typeof response === 'object') {
        emit('saved', response.data.body.id)
        close()
      }
    }
  }

  onBeforeMount(() => {
    if (form.state.branch_id) {
      loadFormData(form.state.branch_id)
    } else {
      loadFormData()
    }
  })

  watch(() => form.state.branch_id, newValue => {
    form.state.pos_register_id = null
    meta.value.posRegisters = []
    if (newValue) {
      loadFormData(newValue)
    }
  })

  const loadFormData = async (branchId?: number) => {
    try {
      const response = (await getFormMeta(branchId)).data.body
      meta.value.branches = response.branches || meta.value.branches
      if (branchId) {
        meta.value.posRegisters = response.pos_registers
      }
    } catch {
    /* Empty */
    }
  }

</script>

<template>
  <VDialog max-width="500" :model-value="modelValue" persistent @update:model-value="emit('update:modelValue', $event)">
    <VForm @submit.prevent="submit">
      <VCard>
        <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-1 font-weight-bold text-h6">
          <VIcon color="primary" icon="tabler-login" size="20" />
          {{ t('pos::pos_sessions.open_session') }}
        </VCardTitle>
        <VCardText class="pt-0">
          <VRow>
            <VCol v-if="!branchId && !user?.assigned_to_branch" cols="12">
              <VSelect
                v-model="form.state.branch_id as unknown as null | undefined"
                :error="!!form.errors.value?.branch_id"
                :error-messages="form.errors.value?.branch_id"
                item-title="name"
                item-value="id"
                :items="meta.branches"
                :label="t('pos::attributes.pos_sessions.branch_id')"
              />
            </VCol>
            <VCol v-if="!posRegisterId" cols="12">
              <VSelect
                v-model="form.state.pos_register_id as unknown as null | undefined"
                :error="!!form.errors.value?.pos_register_id"
                :error-messages="form.errors.value?.pos_register_id"
                item-title="name"
                item-value="id"
                :items="meta.posRegisters"
                :label="t('pos::attributes.pos_sessions.pos_register_id')"
              />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="form.state.opening_float"
                :error="!!form.errors.value?.opening_float"
                :error-messages="form.errors.value?.opening_float"
                :label="t('pos::attributes.pos_sessions.opening_float')"
                type="number"
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="form.state.notes"
                auto-grow
                clearable
                :error="!!form.errors.value?.notes"
                :error-messages="form.errors.value?.notes"
                :label="t('pos::attributes.pos_sessions.notes')"
                rows="2"
              />
            </VCol>
          </VRow>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn color="default" :disabled="form.loading.value" @click="close">
            {{ t('admin::admin.buttons.cancel') }}
          </VBtn>
          <VBtn
            color="primary"
            :disabled="form.loading.value"
            :loading="form.loading.value"
            @click="submit"
          >
            {{ t('admin::admin.buttons.submit') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VForm>
  </VDialog>
</template>
