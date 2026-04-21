<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { usePosSession } from '@/modules/pos/composables/posSession.ts'
  import { useForm } from '@/modules/core/composables/form.ts'

  const props = defineProps<{ modelValue: boolean, item: any }>()
  const emit = defineEmits(['update:modelValue', 'saved'])

  const { t } = useI18n()
  const { close: closeSession } = usePosSession()
  const form = useForm({
    declared_cash: null,
  })

  function close () {
    emit('update:modelValue', false)
  }

  const submit = async () => {
    if (!form.loading.value && await form.submit(() => closeSession(props.item.id, form.state))) {
      emit('saved')
      close()
    }
  }

</script>

<template>
  <VDialog max-width="500" :model-value="modelValue" persistent @update:model-value="emit('update:modelValue', $event)">
    <VForm @submit.prevent="submit">
      <VCard>
        <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-1 font-weight-bold text-h6">
          <VIcon color="primary" icon="tabler-logout" size="20" />
          {{ t('pos::pos_sessions.close_session') }}
        </VCardTitle>
        <VCardText class="pt-0">
          <VTextField
            v-model="form.state.declared_cash"
            :error="!!form.errors.value?.declared_cash"
            :error-messages="form.errors.value?.declared_cash"
            :label="t('pos::attributes.pos_sessions.declared_cash')"
            type="number"
          />
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
