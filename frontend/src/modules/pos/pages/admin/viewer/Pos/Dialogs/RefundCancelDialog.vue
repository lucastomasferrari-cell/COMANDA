<script lang="ts" setup>
  import type { RefundPaymentMethod } from '@/modules/cart/composables/cart.ts'
  import { computed, onBeforeMount, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useOrder } from '@/modules/sale/composables/order.ts'

  interface Order {
    id: number
    is_refund: boolean
    has_refund_amount: boolean
    refunded_amount: { formatted: string }
  }

  interface Meta {
    reasons: Array<{ id: number | string, name: string }>
    registers: Array<{ id: number | string, name: string }>
    order: Order
    refundPaymentMethods: RefundPaymentMethod[]
  }

  const props = defineProps<{
    modelValue: boolean
    orderId: number | string
    registerId?: number | null
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'resolved'): void
  }>()

  const { t } = useI18n()
  const toast = useToast()
  const { cancel, refund, getUpdateStatusMeta } = useOrder()

  const form = useForm({
    reason_id: null as number | string | null,
    register_id: props.registerId ?? null,
    refund_payment_method: null as string | number | null,
    gift_card_code: null as string | null,
    note: null as string | null,
  })

  const meta = ref<Meta>({
    reasons: [],
    registers: [],
    order: {
      id: 0,
      is_refund: false,
      has_refund_amount: false,
      refunded_amount: { formatted: '' },
    },
    refundPaymentMethods: [],
  })

  const loading = ref(false)

  const close = () => emit('update:modelValue', false)

  const disabledSubmit = computed(() =>
    form.loading.value
    || !form.state.reason_id
    || !form.state.register_id
    || (meta.value.order.has_refund_amount && !form.state.refund_payment_method)
    || (form.state.refund_payment_method === 'gift_card' && !form.state.gift_card_code),
  )

  const submit = async () => {
    if (
      !disabledSubmit.value
      && (await form.submit(() =>
        meta.value.order.is_refund
          ? refund(props.orderId, form.state)
          : cancel(props.orderId, form.state),
      ))
    ) {
      emit('resolved')
      close()
    }
  }

  onBeforeMount(async () => {
    try {
      loading.value = true
      const response = (await getUpdateStatusMeta(props.orderId)).data.body

      meta.value.reasons = response.reasons
      meta.value.registers = response.pos_registers
      meta.value.order = response.order
      meta.value.refundPaymentMethods = response.refund_payment_methods
    } catch {
      toast.error(t('core::errors.an_unexpected_error_occurred'))
      close()
    } finally {
      loading.value = false
    }
  })

  const updateRefundPaymentMethod = (id: string | number) => {
    form.state.refund_payment_method = id
    if (id !== 'gift_card') {
      form.state.gift_card_code = null
    }
  }
</script>
<template>
  <VDialog
    max-width="600"
    min-height="300"
    :model-value="modelValue"
    :persistent="form.loading.value"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardText>
        <div
          v-if="loading"
          class="d-flex justify-center align-center"
          style="height: 250px;"
        >
          <VProgressCircular color="primary" indeterminate size="50" />
        </div>
        <VRow v-else>
          <VCol cols="12">
            <h3>{{ t(`order::orders.${meta.order.is_refund ? 'refund' : 'cancel'}_order_dialog.title`) }}</h3>
            <p>{{ t(`order::orders.${meta.order.is_refund ? 'refund' : 'cancel'}_order_dialog.description`) }}</p>
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.register_id"
              :disabled="registerId != null"
              :error="!!form.errors.value?.register_id"
              :error-messages="form.errors.value?.register_id"
              item-title="name"
              item-value="id"
              :items="meta.registers"
              :label="t('order::attributes.cancel_or_refund.register_id')"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.reason_id"
              :error="!!form.errors.value?.reason_id"
              :error-messages="form.errors.value?.reason_id"
              item-title="name"
              item-value="id"
              :items="meta.reasons"
              :label="t('order::attributes.cancel_or_refund.reason_id')"
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="form.state.note"
              auto-grow
              clearable
              :error="!!form.errors.value?.note"
              :error-messages="form.errors.value?.note"
              :label="t('order::attributes.cancel_or_refund.note')"
              rows="4"
            />
          </VCol>

          <VCol v-if="meta.order.has_refund_amount" cols="12">
            <div class="px-1 text-body-2 font-weight-bold">
              {{ t('order::orders.amount_to_be_refunded') }}
            </div>
            <div class="py-4 pt-2 px-3">
              <div class="d-flex justify-space-between align-center">
                <div class="text-caption font-weight-bold">
                  {{ t('order::orders.total_refund') }}
                </div>

                <div class="text-h5 font-weight-bold">
                  {{ meta.order.refunded_amount.formatted }}
                </div>
              </div>
              <VDivider class="my-2" />
              <div class="text-caption text-disabled">
                {{ t('order::orders.amount_to_be_refunded_description') }}
              </div>
            </div>

            <div class="px-1 mb-2 text-body-2 font-weight-bold">
              {{ t('order::attributes.cancel_or_refund.refund_payment_method') }}
            </div>
            <div class="payment-method-scroll">
              <div class="scroll-container">
                <div
                  v-for="method in meta.refundPaymentMethods"
                  :key="method.id"
                  class="payment-method-card"
                  :class="{ active: form.state.refund_payment_method === method.id}"
                  @click="updateRefundPaymentMethod(method.id)"
                >
                  <div class="type-info">
                    <VIcon v-if="method.icon" :color="method.color" :icon="method.icon" />
                    <span class="name">{{ method.name }}</span>
                  </div>
                </div>
              </div>
            </div>
            <VTextField
              v-if="form.state.refund_payment_method === 'gift_card'"
              v-model="form.state.gift_card_code"
              class="mt-4"
              :error="!!form.errors.value?.gift_card_code"
              :error-messages="form.errors.value?.gift_card_code"
              :label="t('order::attributes.cancel_or_refund.gift_card_code')"
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
          :disabled="disabledSubmit"
          :loading="form.loading.value"
          @click="submit"
        >
          {{ t('admin::admin.buttons.submit') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.payment-method-scroll {
  overflow-x: auto;
  white-space: nowrap;
  padding: 0.25rem;

  &::-webkit-scrollbar {
    display: none;
  }

  .scroll-container {
    display: flex;
    gap: 0.5rem;
  }

  .payment-method-card {
    border: 1px dashed #e0e0e0;
    border-radius: 8px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.25s ease;
    text-align: center;
    position: relative;
    min-width: 122px;

    &:not(.cursor-not-allowed):hover {
      border-color: rgb(var(--v-theme-primary));
      background-color: rgba(var(--v-theme-primary), 0.03);
    }
  }

  .type-info {
    display: flex;
    align-items: center;
    gap: 8px;

    .name {
      font-size: 0.83rem;
      font-weight: 700;
    }
  }

  .payment-method-card.active {
    border-color: rgb(var(--v-theme-primary));
    background-color: rgba(var(--v-theme-primary), 0.08);
  }

  .payment-method-card-loading {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.04);
  }
}

</style>
