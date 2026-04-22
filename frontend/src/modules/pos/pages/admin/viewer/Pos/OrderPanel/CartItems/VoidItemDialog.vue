<script lang="ts" setup>
  import type { VoidReason } from '@/modules/sale/api/order.api.ts'
  import { computed, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { listVoidReasons, voidOrderProduct } from '@/modules/sale/api/order.api.ts'

  const props = defineProps<{
    modelValue: boolean
    orderId: number | string | null
    orderProductId: number | string | null
    itemName: string
    kitchenFired: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'voided', response: Record<string, any>): void
  }>()

  const { t } = useI18n()
  const toast = useToast()

  const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
  })

  const reasons = ref<VoidReason[]>([])
  const loadingReasons = ref(false)
  const reasonId = ref<number | null>(null)
  const note = ref<string>('')
  const saving = ref(false)

  const selectedReason = computed(() =>
    reasons.value.find(r => r.id === reasonId.value) ?? null,
  )
  const isOtherReason = computed(() =>
    selectedReason.value?.code?.includes('other') ?? false,
  )
  const needsApproval = computed(() =>
    selectedReason.value?.requires_manager_approval
    ?? props.kitchenFired,
  )

  async function loadReasons () {
    loadingReasons.value = true
    try {
      const res = await listVoidReasons('item')
      reasons.value = res.data.body ?? []
    } catch {
      toast.error(t('core::errors.an_unexpected_error_occurred'))
    } finally {
      loadingReasons.value = false
    }
  }

  watch(open, val => {
    if (val) {
      reasonId.value = null
      note.value = ''
      if (reasons.value.length === 0) loadReasons()
    }
  })

  const canSubmit = computed(() => {
    // Si el item ya fue enviado a cocina, razón obligatoria.
    if (props.kitchenFired && !reasonId.value) return false
    // Si la razón seleccionada es "Otro", note >= 20.
    if (isOtherReason.value && note.value.trim().length < 20) return false
    return true
  })

  async function submit () {
    if (!canSubmit.value || saving.value || !props.orderId || !props.orderProductId) return
    saving.value = true
    try {
      const res = await voidOrderProduct(props.orderId, props.orderProductId, {
        void_reason_id: reasonId.value,
        void_note: note.value.trim() || null,
      })
      toast.success(res.data?.message ?? t('pos::pos_viewer.void_item.saved'))
      emit('voided', res.data.body)
      open.value = false
    } catch (err: any) {
      const msg = err?.response?.data?.message
      const errors = err?.response?.data?.errors
      const first = errors ? Object.values(errors as Record<string, string[]>)[0]?.[0] : null
      toast.error(first || msg || t('core::errors.an_unexpected_error_occurred'))
    } finally {
      saving.value = false
    }
  }
</script>

<template>
  <VDialog v-model="open" max-width="440" persistent>
    <VCard class="pa-2">
      <VCardText>
        <div class="d-flex align-center mb-2">
          <VIcon class="me-2" color="error" icon="tabler-circle-x" size="22" />
          <h3 class="text-h6">{{ t('pos::pos_viewer.void_item.title') }}</h3>
          <VSpacer />
          <VBtn density="compact" icon="tabler-x" variant="text" @click="open = false" />
        </div>

        <p class="text-body-2 mb-3">
          {{ t('pos::pos_viewer.void_item.subtitle', { item: itemName }) }}
        </p>

        <VSelect
          v-model="reasonId"
          class="mb-3"
          :items="reasons"
          item-title="name"
          item-value="id"
          :label="kitchenFired
            ? t('pos::pos_viewer.void_item.reason_required')
            : t('pos::pos_viewer.void_item.reason_optional')"
          :loading="loadingReasons"
          variant="outlined"
        >
          <template #item="{ props: itemProps, item }">
            <VListItem v-bind="itemProps">
              <template v-if="item.raw.requires_manager_approval" #append>
                <VIcon color="warning" icon="tabler-shield-lock" size="16" />
              </template>
            </VListItem>
          </template>
        </VSelect>

        <VTextarea
          v-model="note"
          class="mb-3"
          :hint="isOtherReason ? t('pos::pos_viewer.void_item.note_required_hint') : ''"
          :label="isOtherReason
            ? t('pos::pos_viewer.void_item.note_required')
            : t('pos::pos_viewer.void_item.note_optional')"
          persistent-hint
          rows="2"
          variant="outlined"
        />

        <VAlert
          v-if="needsApproval"
          class="mb-3"
          color="warning"
          density="compact"
          icon="tabler-shield-lock"
          variant="tonal"
        >
          {{ t('pos::pos_viewer.void_item.needs_approval') }}
        </VAlert>

        <div class="d-flex ga-2">
          <VBtn block color="grey-500" variant="tonal" @click="open = false">
            {{ t('admin::admin.buttons.cancel') }}
          </VBtn>
          <VBtn
            block
            color="error"
            :disabled="!canSubmit"
            :loading="saving"
            @click="submit"
          >
            {{ t('pos::pos_viewer.void_item.confirm') }}
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>
