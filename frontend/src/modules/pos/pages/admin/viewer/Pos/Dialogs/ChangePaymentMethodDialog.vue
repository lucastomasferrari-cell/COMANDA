<script lang="ts" setup>
  import type { PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { http } from '@/modules/core/api/http.ts'

  /**
   * Cambio de forma de pago post-cobro. La UI no tipea el PIN — el
   * axios interceptor detecta el 403 con code=manager_approval_required
   * y muestra el GlobalManagerPinDialog. Este dialog solo recolecta
   * reason + new_method + payment.
   */
  const props = defineProps<{
    modelValue: boolean
    orderId: number | string | null
    payments: Array<{ id: number, method: { id?: string, name?: string } | string, amount: { formatted?: string } }>
    meta: PosMeta
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'saved', response: Record<string, any>): void
  }>()

  const { t } = useI18n()
  const toast = useToast()

  const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
  })

  const paymentId = ref<number | null>(null)
  const newMethod = ref<string | null>(null)
  const reason = ref<string>('')
  const saving = ref(false)

  // Métodos disponibles: usamos meta.refundPaymentMethods como fuente
  // (comparten catálogo con los refunds).
  const availableMethods = computed<Array<{ id: string, name: string }>>(() => {
    const methods = (props.meta?.refundPaymentMethods ?? []) as any[]
    return methods.map(m => ({
      id: m.id ?? m,
      name: m.name ?? m,
    }))
  })

  watch(open, val => {
    if (val) {
      paymentId.value = props.payments[0]?.id ?? null
      newMethod.value = null
      reason.value = ''
    }
  })

  const canSubmit = computed(() =>
    !!paymentId.value
    && !!newMethod.value
    && reason.value.trim().length >= 20,
  )

  async function submit () {
    if (!canSubmit.value || saving.value || !props.orderId) return
    saving.value = true
    try {
      const res = await http.patch(`/v1/orders/${props.orderId}/payment-method`, {
        payment_id: paymentId.value,
        new_method: newMethod.value,
        reason: reason.value.trim(),
        // manager_approval_token lo agrega el interceptor automáticamente.
        manager_approval_token: 'pending',
      })
      toast.success(res.data?.message ?? t('pos::pos_viewer.change_payment_method.saved'))
      emit('saved', res.data.body)
      open.value = false
    } catch (err: any) {
      const msg = err?.response?.data?.message
      toast.error(msg ?? t('core::errors.an_unexpected_error_occurred'))
    } finally {
      saving.value = false
    }
  }
</script>

<template>
  <VDialog v-model="open" max-width="480" persistent>
    <VCard class="pa-2">
      <VCardText>
        <div class="d-flex align-center mb-2">
          <VIcon class="me-2" color="warning" icon="tabler-receipt-refund" size="22" />
          <h3 class="text-h6">{{ t('pos::pos_viewer.change_payment_method.title') }}</h3>
          <VSpacer />
          <VBtn density="compact" icon="tabler-x" variant="text" @click="open = false" />
        </div>

        <VAlert class="mb-3" color="warning" density="compact" variant="tonal">
          {{ t('pos::pos_viewer.change_payment_method.audit_notice') }}
        </VAlert>

        <VSelect
          v-model="paymentId"
          class="mb-2"
          item-title="label"
          item-value="id"
          :items="payments.map(p => ({
            id: p.id,
            label: `${typeof p.method === 'string' ? p.method : (p.method?.name ?? '')} — ${p.amount?.formatted ?? ''}`,
          }))"
          :label="t('pos::pos_viewer.change_payment_method.payment')"
          variant="outlined"
        />

        <VSelect
          v-model="newMethod"
          class="mb-2"
          item-title="name"
          item-value="id"
          :items="availableMethods"
          :label="t('pos::pos_viewer.change_payment_method.new_method')"
          variant="outlined"
        />

        <VTextarea
          v-model="reason"
          :hint="t('pos::pos_viewer.change_payment_method.reason_hint')"
          :label="t('pos::pos_viewer.change_payment_method.reason')"
          persistent-hint
          rows="3"
          variant="outlined"
        />

        <div class="d-flex ga-2 mt-4">
          <VBtn block color="grey-500" variant="tonal" @click="open = false">
            {{ t('admin::admin.buttons.cancel') }}
          </VBtn>
          <VBtn
            block
            color="warning"
            :disabled="!canSubmit"
            :loading="saving"
            @click="submit"
          >
            {{ t('pos::pos_viewer.change_payment_method.submit') }}
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>
